<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\Assignment;
use App\Models\Exam;
use App\Models\SemesterGrade;
use App\Models\User;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $guruCount = User::where('role', 'guru')->count();
        $muridCount = User::where('role', 'murid')->count();
        $attendanceToday = Attendance::whereDate('attendance_date', today())->count();
        $averageGrade = round(
            (float) SemesterGrade::avg('score'),
            2
        );

        $recentAssignments = Assignment::with('subject')
            ->latest()
            ->take(5)
            ->get();

        $upcomingExams = Exam::with('subject')
            ->whereDate('start_at', '>=', today())
            ->orderBy('start_at')
            ->take(5)
            ->get();

        $attendanceChart = Attendance::selectRaw('attendance_date, count(*) as total')
            ->whereBetween('attendance_date', [now()->subDays(6), now()])
            ->groupBy('attendance_date')
            ->orderBy('attendance_date')
            ->get()
            ->map(fn ($item) => [
                'date' => $item->attendance_date->format('d M'),
                'total' => $item->total,
            ]);

        return view('admin.dashboard', [
            'guruCount' => $guruCount,
            'muridCount' => $muridCount,
            'attendanceToday' => $attendanceToday,
            'averageGrade' => $averageGrade,
            'recentAssignments' => $recentAssignments,
            'upcomingExams' => $upcomingExams,
            'attendanceChart' => $attendanceChart,
        ]);
    }
}

