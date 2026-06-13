<?php

use App\Http\Controllers\AccountController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\ContactBookController;
use App\Http\Controllers\CourseController;
use App\Http\Controllers\CourseScheduleController;
use App\Http\Controllers\CreditController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\LeaveController;
use App\Http\Controllers\LessonLogController;
use App\Http\Controllers\ParentController;
use App\Http\Controllers\ParentTokenController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\RfidCardController;
use App\Http\Controllers\StudentController;
use Illuminate\Support\Facades\Route;

Route::get('/', fn () => redirect()->route('dashboard'));

Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // === 學生 ===
    Route::resource('students', StudentController::class)->except('destroy');
    Route::post('students/{student}/rfid-cards', [RfidCardController::class, 'store'])->name('students.rfid.store');
    Route::delete('students/{student}/rfid-cards/{card}', [RfidCardController::class, 'destroy'])->name('students.rfid.destroy');

    // === 課程 + 固定時段 ===
    Route::resource('courses', CourseController::class)->except('destroy');
    Route::post('courses/{course}/schedules', [CourseScheduleController::class, 'store'])->name('courses.schedules.store');
    Route::put('courses/{course}/schedules/{schedule}', [CourseScheduleController::class, 'update'])->name('courses.schedules.update');
    Route::delete('courses/{course}/schedules/{schedule}', [CourseScheduleController::class, 'destroy'])->name('courses.schedules.destroy');

    // === 點名工作檯 ===
    Route::get('attendance', [AttendanceController::class, 'index'])->name('attendance.index');
    Route::post('attendance/scan', [AttendanceController::class, 'scan'])->name('attendance.scan');

    // === 聯絡簿（班級日誌 + 個人聯絡簿）===
    Route::get('lesson-logs', [LessonLogController::class, 'index'])->name('lesson-logs.index');
    Route::post('lesson-logs', [LessonLogController::class, 'store'])->name('lesson-logs.store');
    Route::get('lesson-logs/{lessonLog}', [LessonLogController::class, 'show'])->name('lesson-logs.show');
    Route::delete('lesson-logs/{lessonLog}', [LessonLogController::class, 'destroy'])->name('lesson-logs.destroy');
    Route::get('contact-books', [ContactBookController::class, 'index'])->name('contact-books.index');
    Route::post('contact-books', [ContactBookController::class, 'store'])->name('contact-books.store');
    Route::delete('contact-books/{contactBook}', [ContactBookController::class, 'destroy'])->name('contact-books.destroy');

    // === 報表 ===
    Route::get('reports', [ReportController::class, 'index'])->name('reports.index');
    Route::get('reports/credit-transactions', [ReportController::class, 'creditTransactions'])->name('reports.credit-transactions');
    Route::get('reports/leave-records', [ReportController::class, 'leaveRecords'])->name('reports.leave-records');

    // === 請假管理 ===
    Route::get('leave', [LeaveController::class, 'index'])->name('leave.index');
    Route::post('leave', [LeaveController::class, 'store'])->name('leave.store');
    Route::put('leave/{leave}', [LeaveController::class, 'update'])->name('leave.update');
    Route::delete('leave/{leave}', [LeaveController::class, 'destroy'])->name('leave.destroy');

    // === 點數帳戶 ===
    Route::get('credits', [CreditController::class, 'index'])->name('credits.index');
    Route::get('credits/{enrollment}', [CreditController::class, 'show'])->name('credits.show');
    Route::post('credits/purchase', [CreditController::class, 'purchase'])->name('credits.purchase');
    Route::post('credits/{enrollment}/adjust', [CreditController::class, 'adjust'])->name('credits.adjust');

    // === 家長 ===
    Route::resource('parents', ParentController::class)->except('destroy');
    Route::post('parents/{parent}/children', [ParentController::class, 'attachChild'])->name('parents.children.attach');
    Route::delete('parents/{parent}/children/{student}', [ParentController::class, 'detachChild'])->name('parents.children.detach');
    Route::post('parents/{parent}/token', [ParentTokenController::class, 'store'])->name('parents.token.store');
    Route::delete('parents/{parent}/token', [ParentTokenController::class, 'destroy'])->name('parents.token.destroy');

    // === 僅管理員：刪除 + 帳號管理 ===
    Route::middleware('admin')->group(function () {
        Route::delete('students/{student}', [StudentController::class, 'destroy'])->name('students.destroy');
        Route::delete('parents/{parent}', [ParentController::class, 'destroy'])->name('parents.destroy');
        Route::delete('courses/{course}', [CourseController::class, 'destroy'])->name('courses.destroy');
        Route::resource('accounts', AccountController::class)->except('show');
    });

    // 個人資料（Breeze）
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// === 家長 Portal（公開，靠 token 驗身分，無需登入）===
Route::get('p/{token}', [\App\Http\Controllers\ParentPortalController::class, 'home'])->name('portal.home');
Route::get('p/{token}/students/{student}', [\App\Http\Controllers\ParentPortalController::class, 'student'])->name('portal.student');
Route::post('p/{token}/students/{student}/leave', [\App\Http\Controllers\ParentPortalController::class, 'leaveStore'])->name('portal.leave');

require __DIR__.'/auth.php';
