<?php

namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\Assignment;
use App\Models\Exam;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $guru = Auth::user();
        $subjects = $guru->subjectsTeaching()->withCount(['students'])->get();

        $todayAttendance = Attendance::with('student')
            ->whereDate('attendance_date', today())
            ->whereIn('subject_id', $subjects->pluck('id'))
            ->get();

        $latestAssignments = Assignment::with('subject')
            ->where('guru_id', $guru->id)
            ->latest()
            ->take(5)
            ->get();

        $upcomingExams = Exam::with('subject')
            ->where('creator_id', $guru->id)
            ->whereDate('start_at', '>=', today())
            ->orderBy('start_at')
            ->take(5)
            ->get();

        return view('guru.dashboard', [
            'guru' => $guru,
            'subjects' => $subjects,
            'todayAttendance' => $todayAttendance,
            'latestAssignments' => $latestAssignments,
            'upcomingExams' => $upcomingExams,
        ]);
    }
}

