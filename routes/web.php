<?php

use App\Http\Controllers\Admin\AttendanceController as AdminAttendanceController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\ExamController as AdminExamController;
use App\Http\Controllers\Admin\GradeController as AdminGradeController;
use App\Http\Controllers\Admin\ClassroomController as AdminClassroomController;
use App\Http\Controllers\Admin\ReportController as AdminReportController;
use App\Http\Controllers\Admin\SubjectController as AdminSubjectController;
use App\Http\Controllers\Admin\UserController as AdminUserController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\Guru\AttendanceController as GuruAttendanceController;
use App\Http\Controllers\Guru\DashboardController as GuruDashboardController;
use App\Http\Controllers\Guru\ExamController as GuruExamController;
use App\Http\Controllers\Guru\GradeController as GuruGradeController;
use App\Http\Controllers\Guru\TaskController as GuruTaskController;
use App\Http\Controllers\Murid\AttendanceController as MuridAttendanceController;
use App\Http\Controllers\Murid\DashboardController as MuridDashboardController;
use App\Http\Controllers\Murid\ExamController as MuridExamController;
use App\Http\Controllers\Murid\GradeController as MuridGradeController;
use App\Http\Controllers\Murid\TaskController as MuridTaskController;
use Illuminate\Support\Facades\Route;

Route::get('/', fn () => redirect()->route('login'));

Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.perform');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');
    // Profil Admin
    Route::get('profile/edit', [\App\Http\Controllers\Admin\ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('profile', [\App\Http\Controllers\Admin\ProfileController::class, 'update'])->name('profile.update');

    Route::resource('users', AdminUserController::class);
    Route::post('users/{user}/reset-password', [AdminUserController::class, 'resetPassword'])->name('users.reset_password');
    Route::resource('subjects', AdminSubjectController::class);
    Route::resource('classrooms', AdminClassroomController::class);
    // Admin hanya melihat data absensi
    Route::resource('attendances', AdminAttendanceController::class)->only(['index']);
        // Batasi resource agar tidak bisa tambah/hapus dari UI
        Route::resource('exams', AdminExamController::class)->only(['index', 'edit', 'update']);
    // Admin: fitur melihat hasil ujian dan memberi nilai seperti dosen
    Route::get('exams/{exam}/results', [AdminExamController::class, 'results'])->name('exams.results');
    Route::get('exams/{exam}/results/{result}/download', [AdminExamController::class, 'downloadSubmission'])->name('exams.results.download');
    Route::patch('exams/{exam}/results/{result}', [AdminExamController::class, 'gradeResult'])->name('exams.results.update');
    // Admin nilai: tampil mirip guru dan bisa input
    Route::get('grades', [AdminGradeController::class, 'index'])->name('grades.index');
    Route::get('grades/summary', [AdminGradeController::class, 'summary'])->name('grades.summary');
    Route::get('grades/export/pdf', [AdminGradeController::class, 'exportSummaryPdf'])->name('grades.export.pdf');
    Route::get('grades/export/excel', [AdminGradeController::class, 'exportSummaryExcel'])->name('grades.export.excel');
    Route::get('grades/student/{student}', [AdminGradeController::class, 'showStudent'])->name('grades.student.show');
    Route::post('grades/student/{student}', [AdminGradeController::class, 'saveStudent'])->name('grades.student.save');
    Route::get('grades/student/{student}/export', [AdminGradeController::class, 'exportStudentPdf'])->name('grades.student.export');

    Route::get('reports/attendance', [AdminReportController::class, 'attendance'])->name('reports.attendance');
    Route::get('reports/grades', [AdminReportController::class, 'grades'])->name('reports.grades');
});

Route::middleware(['auth', 'role:guru'])->prefix('guru')->name('guru.')->group(function () {
    Route::get('dashboard', [GuruDashboardController::class, 'index'])->name('dashboard');
    // Profil Guru
    Route::get('profile/edit', [\App\Http\Controllers\Guru\ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('profile', [\App\Http\Controllers\Guru\ProfileController::class, 'update'])->name('profile.update');
    Route::patch('tasks/{task}/submissions/{submission}', [GuruTaskController::class, 'gradeSubmission'])->name('tasks.submissions.update');
    Route::resource('tasks', GuruTaskController::class);
    Route::resource('subjects', \App\Http\Controllers\Guru\SubjectController::class)->only(['index', 'create', 'store', 'edit', 'update', 'destroy']);
    Route::resource('attendances', GuruAttendanceController::class)->only(['index', 'create', 'store', 'edit', 'update']);
        // Dosen: full CRUD + hasil ujian
        // Route results harus didefinisikan SEBELUM route resource untuk menghindari konflik
    Route::get('exams/{exam}/results', [GuruExamController::class, 'results'])->name('exams.results');
    Route::get('exams/{exam}/results/{result}/download', [GuruExamController::class, 'downloadSubmission'])->name('exams.results.download');
    Route::patch('exams/{exam}/results/{result}', [GuruExamController::class, 'gradeResult'])->name('exams.results.update');
    Route::resource('exams', GuruExamController::class)->only(['index', 'create', 'store', 'edit', 'update', 'destroy']);
    Route::get('grades', [GuruGradeController::class, 'index'])->name('grades.index');
    Route::get('grades/uts', [GuruGradeController::class, 'uts'])->name('grades.uts');
    Route::get('grades/uas', [GuruGradeController::class, 'uas'])->name('grades.uas');
    Route::get('grades/tugas', [GuruGradeController::class, 'tasks'])->name('grades.tasks');
    Route::get('grades/praktikum', [GuruGradeController::class, 'praktikum'])->name('grades.praktikum');
    Route::get('grades/exams/export', [GuruGradeController::class, 'exportExamGrades'])->name('grades.exams.export');
    Route::get('grades/export/pdf', [GuruGradeController::class, 'exportSummaryPdf'])->name('grades.export.pdf');
    Route::get('grades/export/excel', [GuruGradeController::class, 'exportSummaryExcel'])->name('grades.export.excel');
    Route::get('grades/summary', [GuruGradeController::class, 'summary'])->name('grades.summary');
    // Show & Simpan nilai berbobot per siswa
    Route::get('grades/student/{student}', [GuruGradeController::class, 'showStudent'])->name('grades.student.show');
    Route::post('grades/student/{student}', [GuruGradeController::class, 'saveStudent'])->name('grades.student.save');
    Route::get('grades/student/{student}/export', [GuruGradeController::class, 'exportStudentPdf'])->name('grades.student.export');
});

Route::middleware(['auth', 'role:murid'])->prefix('murid')->name('murid.')->group(function () {
    Route::get('dashboard', [MuridDashboardController::class, 'index'])->name('dashboard');
    // Profil Murid
    Route::get('profile/edit', [\App\Http\Controllers\Murid\ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('profile', [\App\Http\Controllers\Murid\ProfileController::class, 'update'])->name('profile.update');
    Route::resource('tasks', MuridTaskController::class)->only(['index', 'create', 'store']);
    // Route POST untuk exams harus didefinisikan SEBELUM route resource untuk menghindari konflik
    Route::post('exams/{exam}/submit-file', [MuridExamController::class, 'submitFile'])->name('exams.submit_file');
    Route::post('exams/{exam}/submit-text', [MuridExamController::class, 'submitText'])->name('exams.submit_text');
    Route::post('exams/{exam}/heartbeat', [MuridExamController::class, 'heartbeat'])->name('exams.heartbeat');
    Route::post('exams/{exam}/auto-finish', [MuridExamController::class, 'autoFinish'])->name('exams.auto_finish');
    Route::post('exams/{exam}/export-docx', [MuridExamController::class, 'exportDocx'])->name('exams.export_docx');
    Route::resource('exams', MuridExamController::class)->only(['index', 'show', 'store']);
    Route::get('attendances', [MuridAttendanceController::class, 'index'])->name('attendances.index');
    Route::get('grades', [MuridGradeController::class, 'index'])->name('grades.index');
});
