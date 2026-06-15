<?php

namespace Database\Seeders;

use App\Models\Course;
use App\Models\Enrollment;
use App\Models\LeaveRecord;
use App\Models\LessonLog;
use App\Models\ParentAccessToken;
use App\Models\RfidCard;
use App\Models\StudentContactBook;
use App\Models\User;
use App\Services\AttendanceService;
use App\Services\CreditService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 已經灌過 demo 資料就跳過（讓部署平台每次重啟都能安全執行 db:seed，不會重複建資料）
        if (User::where('email', 'admin@demo.com')->exists()) {
            $this->command?->info('demo 資料已存在，略過 seeding。');

            return;
        }

        $credits = app(CreditService::class);
        $attendance = app(AttendanceService::class);

        // === 登入帳號 ===
        $admin = User::create([
            'role' => 'admin', 'name' => '系統管理員',
            'email' => 'admin@demo.com', 'password' => Hash::make('Demo1234'),
        ]);
        $teacher = User::create([
            'role' => 'teacher', 'name' => 'Emily 老師',
            'email' => 'teacher@demo.com', 'password' => Hash::make('Demo1234'),
        ]);

        // === 課程 + 固定時段 ===
        $coursesData = [
            ['name' => '兒童美語 A 班', 'price_per_pack' => 6000, 'class_type' => 'group',
             'sched' => [[1, '16:00', '17:30'], [3, '16:00', '17:30']]],
            ['name' => '兒童美語 B 班', 'price_per_pack' => 6000, 'class_type' => 'group',
             'sched' => [[2, '16:00', '17:30'], [4, '16:00', '17:30']]],
            ['name' => '國中英文文法', 'price_per_pack' => 9000, 'class_type' => 'group',
             'sched' => [[1, '18:00', '20:00'], [5, '18:00', '20:00']]],
            ['name' => '會話進階班', 'price_per_pack' => 9500, 'class_type' => 'private',
             'sched' => [[6, '10:00', '12:00']]],
            ['name' => '自然發音入門', 'price_per_pack' => 5500, 'class_type' => 'group',
             'sched' => [[3, '17:30', '18:30']]],
        ];

        $courses = collect();
        foreach ($coursesData as $c) {
            $course = Course::create([
                'name' => $c['name'], 'credits_per_pack' => 20,
                'price_per_pack' => $c['price_per_pack'], 'class_type' => $c['class_type'],
            ]);
            foreach ($c['sched'] as [$wd, $start, $end]) {
                $course->schedules()->create([
                    'weekday' => $wd, 'start_time' => $start, 'end_time' => $end, 'room' => 'A 教室',
                ]);
            }
            $courses->push($course);
        }

        // === 家長 ===
        $parents = collect();
        foreach (['林媽媽', '陳爸爸', '黃媽媽'] as $i => $pname) {
            $parent = User::create([
                'role' => 'parent', 'name' => $pname,
                'email' => "parent{$i}@demo.com", 'phone' => '09' . rand(10000000, 99999999),
                'password' => Hash::make('Demo1234'),
            ]);
            ParentAccessToken::create([
                'parent_id' => $parent->id,
                'token' => ParentAccessToken::generateToken(),
                'created_by' => $admin->id,
            ]);
            $parents->push($parent);
        }

        // === 學生 ===
        $names = ['王小明', '李美華', '張家豪', '陳怡君', '林志偉', '黃淑芬', '吳俊傑', '蔡欣怡',
                  '鄭文彬', '謝佳穎', '許志明', '楊雅婷', '周建宏', '劉品妍', '洪世昌'];
        $grades = ['國小三', '國小四', '國小五', '國小六', '國一', '國二', '國三'];

        foreach ($names as $i => $name) {
            $student = User::create([
                'role' => 'student', 'name' => $name,
                'phone' => '09' . rand(10000000, 99999999),
                'grade_level' => $grades[$i % count($grades)],
                'is_active' => $i < 13,
            ]);

            // RFID 卡
            RfidCard::create([
                'student_id' => $student->id,
                'card_uid' => 'CARD-' . str_pad((string) ($i + 1), 4, '0', STR_PAD_LEFT),
                'label' => '悠遊卡',
            ]);

            // 綁定家長（前 9 個學生輪流綁 3 位家長）
            if ($i < 9) {
                \App\Models\StudentParent::create([
                    'student_id' => $student->id,
                    'parent_id'  => $parents[$i % 3]->id,
                    'relation'   => '家長',
                ]);
            }

            // 報名 1-2 門課 + 儲值（用 CreditService，產生 purchase 交易）
            $packsByIndex = [0, 1, 1, 1, 2, 3, 5, 8][$i % 8]; // 控制餘額分佈（含點數不足）
            foreach ($courses->random(rand(1, 2)) as $course) {
                $enrollment = Enrollment::firstOrCreate(
                    ['student_id' => $student->id, 'course_id' => $course->id],
                    ['credits_remaining' => 0, 'current_material' => 'Unit ' . rand(1, 8)],
                );
                if ($packsByIndex > 0) {
                    $credits->purchase($student, $course, 1, $admin, [
                        'list_price' => $course->price_per_pack,
                        'paid' => $course->price_per_pack - ($i % 2 ? 500 : 0),
                        'discount' => $i % 2 ? 500 : 0,
                    ], '期初儲值');
                }
                // 把餘額調到目標附近（示範手動加扣點 + 製造點數不足名單）
                $target = [0, 1, 2, 3, 8, 12][$i % 6];
                $diff = $target - $enrollment->fresh()->credits_remaining;
                if ($diff !== 0) {
                    try { $credits->adjust($enrollment->fresh(), $diff, $admin, '期初調整'); } catch (\Throwable $e) {}
                }
            }
        }

        // === 出席：替幾位有課有點數的學生簽到（觸發扣點）===
        $someStudents = User::students()->where('is_active', true)->take(5)->get();
        foreach ($someStudents as $student) {
            $enrollment = $student->enrollments()->where('credits_remaining', '>', 0)->first();
            if ($enrollment) {
                try { $attendance->checkIn($student, $enrollment); } catch (\Throwable $e) {}
            }
        }

        // === 請假紀錄 ===
        foreach (User::students()->where('is_active', true)->take(4)->get() as $k => $student) {
            $enrollment = $student->enrollments()->first();
            if ($enrollment) {
                LeaveRecord::create([
                    'student_id' => $student->id, 'enrollment_id' => $enrollment->id,
                    'leave_date' => Carbon::today()->subDays($k + 1),
                    'reason' => ['生病', '家庭旅遊', '臨時有事', '發燒'][$k],
                    'is_made_up' => $k % 2 === 0,
                    'made_up_date' => $k % 2 === 0 ? Carbon::today()->addDays($k) : null,
                    'created_by' => $admin->id,
                ]);
            }
        }

        // === 班級聯絡簿 ===
        foreach ($courses->take(3) as $course) {
            LessonLog::create([
                'course_id' => $course->id, 'log_date' => Carbon::today(),
                'summary' => "今天上了 {$course->name} 的新單元，小朋友表現很好。",
                'homework' => '習作 p.12-15，明天小考單字。',
                'created_by' => $teacher->id,
            ]);
        }

        // === 個人聯絡簿 ===
        foreach (User::students()->where('is_active', true)->take(5)->get() as $student) {
            $enrollment = $student->enrollments()->with('course')->first();
            StudentContactBook::create([
                'student_id' => $student->id,
                'course_id' => $enrollment?->course_id,
                'lesson_date' => Carbon::today(),
                'content' => "{$student->name} 今天上課專注，發音進步明顯。",
                'homework' => '回家複習單字 10 個',
                'created_by' => $teacher->id,
            ]);
        }
    }
}
