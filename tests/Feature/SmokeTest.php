<?php

namespace Tests\Feature;

use App\Models\Course;
use App\Models\Enrollment;
use App\Models\User;
use App\Services\AttendanceService;
use App\Services\CreditService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SmokeTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::firstOrCreate(['email' => 'a@t.com'], ['role' => 'admin', 'name' => '管理員', 'password' => bcrypt('x')]);
    }

    private function student(): User
    {
        return User::create(['role' => 'student', 'name' => '學生', 'is_active' => true]);
    }

    private function course(): Course
    {
        return Course::create(['name' => '測試課', 'credits_per_pack' => 20, 'price_per_pack' => 6000]);
    }

    public function test_guest_redirected_to_login(): void
    {
        $this->get('/dashboard')->assertRedirect('/login');
    }

    public function test_dashboard_renders(): void
    {
        $this->actingAs($this->admin())->get('/dashboard')->assertOk()->assertSee('總覽');
    }

    public function test_purchase_adds_credits_and_logs_transaction(): void
    {
        $admin = $this->admin();
        $student = $this->student();
        $course = $this->course();

        $enrollment = app(CreditService::class)->purchase($student, $course, 2, $admin);

        // 2 包 × 20 點 = 40
        $this->assertEquals(40, $enrollment->credits_remaining);
        $this->assertDatabaseHas('credit_transactions', [
            'enrollment_id' => $enrollment->id, 'tx_type' => 'purchase', 'amount' => 40, 'balance_after' => 40,
        ]);
    }

    public function test_checkin_deducts_one_credit_like_trigger(): void
    {
        $student = $this->student();
        $course = $this->course();
        $enrollment = Enrollment::create(['student_id' => $student->id, 'course_id' => $course->id, 'credits_remaining' => 3]);

        $record = app(AttendanceService::class)->checkIn($student, $enrollment);

        $this->assertEquals('check_in', $record->record_type);
        $this->assertEquals(2, $enrollment->fresh()->credits_remaining);
        $this->assertDatabaseHas('credit_transactions', [
            'enrollment_id' => $enrollment->id, 'tx_type' => 'check_in', 'amount' => -1, 'balance_after' => 2,
        ]);
    }

    public function test_checkin_blocked_when_no_credits(): void
    {
        $student = $this->student();
        $course = $this->course();
        $enrollment = Enrollment::create(['student_id' => $student->id, 'course_id' => $course->id, 'credits_remaining' => 0]);

        $this->expectException(\RuntimeException::class);
        app(AttendanceService::class)->checkIn($student, $enrollment);

        // 整筆 rollback：沒有產生簽到紀錄
        $this->assertDatabaseCount('attendance_records', 0);
    }

    // === Phase B ===
    public function test_phase_b_pages_render(): void
    {
        $admin = $this->admin();
        $this->student();
        User::create(['role' => 'parent', 'name' => '家長', 'email' => 'p@t.com', 'password' => bcrypt('x')]);

        $this->actingAs($admin)->get('/students')->assertOk()->assertSee('學生管理');
        $this->actingAs($admin)->get('/parents')->assertOk()->assertSee('家長管理');
        $this->actingAs($admin)->get('/accounts')->assertOk()->assertSee('帳號管理');
    }

    public function test_rfid_card_can_be_bound_to_student(): void
    {
        $student = $this->student();

        $this->actingAs($this->admin())
            ->post(route('students.rfid.store', $student), ['card_uid' => 'TESTUID-1', 'label' => '悠遊卡'])
            ->assertRedirect();

        $this->assertDatabaseHas('rfid_cards', ['student_id' => $student->id, 'card_uid' => 'TESTUID-1']);
    }

    public function test_parent_token_generation_revokes_old(): void
    {
        $parent = User::create(['role' => 'parent', 'name' => '家長', 'email' => 'p2@t.com', 'password' => bcrypt('x')]);
        $admin = $this->admin();

        $this->actingAs($admin)->post(route('parents.token.store', $parent))->assertRedirect();
        $this->actingAs($admin)->post(route('parents.token.store', $parent))->assertRedirect();

        // 重新產生後只剩 1 個 active token
        $this->assertEquals(1, $parent->accessTokens()->where('is_active', true)->count());
        $this->assertEquals(2, $parent->accessTokens()->count());
    }

    public function test_accounts_management_is_admin_only(): void
    {
        $teacher = User::create(['role' => 'teacher', 'name' => '老師', 'email' => 't@t.com', 'password' => bcrypt('x')]);

        $this->actingAs($teacher)->get('/accounts')->assertForbidden();
        $this->actingAs($this->admin())->get('/accounts')->assertOk();
    }

    // === Phase C ===
    public function test_courses_pages_render(): void
    {
        $course = $this->course();
        $admin = $this->admin();
        $this->actingAs($admin)->get('/courses')->assertOk()->assertSee('課程管理');
        $this->actingAs($admin)->get(route('courses.show', $course))->assertOk()->assertSee('未來 20 天課表預覽');
    }

    public function test_course_schedule_can_be_added_and_validates_time_order(): void
    {
        $course = $this->course();
        $admin = $this->admin();

        $this->actingAs($admin)->post(route('courses.schedules.store', $course), [
            'weekday' => 1, 'start_time' => '16:00', 'end_time' => '17:30', 'room' => 'A',
        ])->assertRedirect();
        $this->assertDatabaseHas('course_schedules', ['course_id' => $course->id, 'weekday' => 1]);

        // end <= start 應被擋
        $this->actingAs($admin)->post(route('courses.schedules.store', $course), [
            'weekday' => 2, 'start_time' => '18:00', 'end_time' => '17:00',
        ])->assertSessionHasErrors('end_time');
    }

    public function test_upcoming_sessions_computed_from_schedules(): void
    {
        $course = $this->course();
        // 每週一、三各一段
        $course->schedules()->create(['weekday' => 1, 'start_time' => '16:00', 'end_time' => '17:00']);
        $course->schedules()->create(['weekday' => 3, 'start_time' => '16:00', 'end_time' => '17:00']);

        $sessions = $course->fresh()->upcomingSessions(14);

        // 兩週內，週一+週三 → 應有 4 場
        $this->assertCount(4, $sessions);
    }

    // === Phase D ===
    public function test_credits_index_renders(): void
    {
        $this->actingAs($this->admin())->get('/credits')->assertOk()->assertSee('點數帳戶');
    }

    public function test_purchase_via_controller_creates_account_and_credits(): void
    {
        $admin = $this->admin();
        $student = $this->student();
        $course = $this->course(); // 20 點/包

        $this->actingAs($admin)->post(route('credits.purchase'), [
            'student_id' => $student->id, 'course_id' => $course->id, 'packs' => 2, 'paid' => 12000,
        ])->assertRedirect();

        $this->assertDatabaseHas('enrollments', ['student_id' => $student->id, 'course_id' => $course->id, 'credits_remaining' => 40]);
        $this->assertDatabaseHas('credit_transactions', ['tx_type' => 'purchase', 'amount' => 40, 'paid_amount' => 12000]);
    }

    public function test_adjust_deduct_cannot_exceed_balance(): void
    {
        $student = $this->student();
        $course = $this->course();
        $enrollment = Enrollment::create(['student_id' => $student->id, 'course_id' => $course->id, 'credits_remaining' => 2]);

        $this->actingAs($this->admin())
            ->post(route('credits.adjust', $enrollment), ['direction' => 'deduct', 'amount' => 5])
            ->assertSessionHasErrors('amount');

        $this->assertEquals(2, $enrollment->fresh()->credits_remaining);
    }

    // === Phase E ===
    public function test_attendance_workbench_renders(): void
    {
        $this->actingAs($this->admin())->get('/attendance')->assertOk()->assertSee('點名工作檯');
    }

    public function test_scan_checks_in_and_deducts_credit(): void
    {
        $student = $this->student();
        $course = $this->course();
        $enrollment = Enrollment::create(['student_id' => $student->id, 'course_id' => $course->id, 'credits_remaining' => 5]);
        \App\Models\RfidCard::create(['student_id' => $student->id, 'card_uid' => 'SCAN-1']);

        $this->actingAs($this->admin())
            ->post(route('attendance.scan'), ['card_uid' => 'SCAN-1'])
            ->assertRedirect()->assertSessionHas('scan_result');

        $this->assertEquals(4, $enrollment->fresh()->credits_remaining);
        $this->assertDatabaseHas('attendance_records', ['student_id' => $student->id, 'record_type' => 'check_in']);
    }

    public function test_scan_unknown_card_shows_error(): void
    {
        $this->actingAs($this->admin())
            ->post(route('attendance.scan'), ['card_uid' => 'NOPE'])
            ->assertSessionHas('scan_error');
    }

    // === Phase F ===
    public function test_lesson_log_pages_render(): void
    {
        $this->actingAs($this->admin())->get('/lesson-logs')->assertOk()->assertSee('班級日誌');
        $this->actingAs($this->admin())->get('/contact-books')->assertOk()->assertSee('個人聯絡簿');
    }

    public function test_lesson_log_created_with_photo_upload(): void
    {
        \Illuminate\Support\Facades\Storage::fake('public');
        $course = $this->course();

        $this->actingAs($this->admin())->post(route('lesson-logs.store'), [
            'course_id' => $course->id,
            'log_date'  => now()->format('Y-m-d'),
            'summary'   => '今天上了 Unit 5',
            'photos'    => [\Illuminate\Http\UploadedFile::fake()->image('class.jpg')],
        ])->assertRedirect();

        $this->assertDatabaseCount('lesson_logs', 1);
        $this->assertDatabaseCount('lesson_log_photos', 1);
        $photo = \App\Models\LessonLogPhoto::first();
        \Illuminate\Support\Facades\Storage::disk('public')->assertExists($photo->storage_path);
    }

    public function test_contact_book_entry_created(): void
    {
        $student = $this->student();

        $this->actingAs($this->admin())->post(route('contact-books.store'), [
            'student_id'  => $student->id,
            'lesson_date' => now()->format('Y-m-d'),
            'content'     => '今天表現很好',
        ])->assertRedirect();

        $this->assertDatabaseHas('student_contact_books', ['student_id' => $student->id]);
    }

    // === Phase G ===
    public function test_leave_flow_create_and_mark_made_up(): void
    {
        $student = $this->student();
        $course = $this->course();
        $enrollment = Enrollment::create(['student_id' => $student->id, 'course_id' => $course->id, 'credits_remaining' => 5]);
        $admin = $this->admin();

        $this->actingAs($admin)->get('/leave')->assertOk()->assertSee('請假管理');

        $this->actingAs($admin)->post(route('leave.store'), [
            'enrollment_id' => $enrollment->id, 'leave_date' => now()->format('Y-m-d'), 'reason' => '生病',
        ])->assertRedirect();
        $leave = \App\Models\LeaveRecord::first();
        $this->assertNotNull($leave);
        $this->assertFalse($leave->is_made_up);

        // 請假不扣點
        $this->assertEquals(5, $enrollment->fresh()->credits_remaining);

        // 標記補課
        $this->actingAs($admin)->put(route('leave.update', $leave), ['is_made_up' => 1])->assertRedirect();
        $this->assertTrue($leave->fresh()->is_made_up);
        $this->assertNotNull($leave->fresh()->made_up_date);
    }

    // === Phase H ===
    public function test_reports_index_renders_finance(): void
    {
        $this->actingAs($this->admin())->get('/reports')->assertOk()->assertSee('財務總覽');
    }

    public function test_credit_report_csv_has_bom_and_xlsx_is_valid(): void
    {
        $admin = $this->admin();
        $student = $this->student();
        $course = $this->course();
        app(CreditService::class)->purchase($student, $course, 1, $admin, ['paid' => 6000]);

        // CSV：開頭應為 UTF-8 BOM
        $csv = $this->actingAs($admin)->get(route('reports.credit-transactions', ['format' => 'csv']));
        $csv->assertOk();
        $this->assertStringStartsWith("\xEF\xBB\xBF", $csv->streamedContent());

        // XLSX：開頭應為 zip magic（PK）
        $xlsx = $this->actingAs($admin)->get(route('reports.credit-transactions', ['format' => 'xlsx']));
        $xlsx->assertOk();
        $this->assertStringStartsWith('PK', $xlsx->getContent());
        $xlsx->assertHeader('content-type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    }

    // === Phase I（家長 Portal）===
    private function parentWithChild(User $child): array
    {
        $parent = User::create(['role' => 'parent', 'name' => '家長', 'email' => 'pp@t.com', 'password' => bcrypt('x')]);
        \App\Models\StudentParent::create(['parent_id' => $parent->id, 'student_id' => $child->id]);
        $token = \App\Models\ParentAccessToken::create(['parent_id' => $parent->id, 'token' => \App\Models\ParentAccessToken::generateToken()]);

        return [$parent, $token->token];
    }

    public function test_portal_home_works_with_valid_token_and_is_public(): void
    {
        $child = $this->student();
        [$parent, $token] = $this->parentWithChild($child);

        // 不需登入即可存取
        $this->get(route('portal.home', $token))->assertOk()->assertSee($child->name);
    }

    public function test_portal_invalid_token_404(): void
    {
        $this->get(route('portal.home', 'bogus-token'))->assertNotFound();
    }

    public function test_portal_blocks_other_parents_child(): void
    {
        $myChild = $this->student();
        $otherChild = User::create(['role' => 'student', 'name' => '別人小孩', 'is_active' => true]);
        [$parent, $token] = $this->parentWithChild($myChild);

        $this->get(route('portal.student', ['token' => $token, 'student' => $otherChild]))->assertForbidden();
    }

    public function test_portal_online_leave_creates_record(): void
    {
        $child = $this->student();
        $course = $this->course();
        $enrollment = Enrollment::create(['student_id' => $child->id, 'course_id' => $course->id, 'credits_remaining' => 5]);
        [$parent, $token] = $this->parentWithChild($child);

        $this->post(route('portal.leave', ['token' => $token, 'student' => $child]), [
            'enrollment_id' => $enrollment->id, 'leave_date' => now()->format('Y-m-d'), 'reason' => '出國',
        ])->assertRedirect();

        $this->assertDatabaseHas('leave_records', ['student_id' => $child->id, 'created_by' => $parent->id]);
    }
}
