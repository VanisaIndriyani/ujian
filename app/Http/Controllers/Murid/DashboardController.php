<?php

namespace App\Http\Controllers\Murid;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\Assignment;
use App\Models\AssignmentSubmission;
use App\Models\Exam;
use App\Models\ExamResult;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $murid = Auth::user();

        $activeAssignments = Assignment::with('subject')
            ->whereIn('subject_id', $murid->subjects()->pluck('subjects.id'))
            ->where(function ($query) {
                $query->whereNull('due_at')->orWhere('due_at', '>=', now());
            })
            ->orderBy('due_at')
            ->take(5)
            ->get();

        $upcomingExams = Exam::with('subject')
            ->whereIn('subject_id', $murid->subjects()->pluck('subjects.id'))
            ->whereDate('start_at', '>=', today())
            ->orderBy('start_at')
            ->take(5)
            ->get();

        $attendanceSummary = Attendance::selectRaw('status, count(*) as total')
            ->where('student_id', $murid->id)
            ->groupBy('status')
            ->pluck('total', 'status');

        $latestScores = ExamResult::with('exam.subject')
            ->where('student_id', $murid->id)
            ->orderByDesc('created_at')
            ->take(5)
            ->get();

        $submittedAssignments = AssignmentSubmission::with('assignment.subject')
            ->where('student_id', $murid->id)
            ->latest()
            ->take(5)
            ->get();

        return view('murid.dashboard', [
            'murid' => $murid,
            'activeAssignments' => $activeAssignments,
            'upcomingExams' => $upcomingExams,
            'attendanceSummary' => $attendanceSummary,
            'latestScores' => $latestScores,
            'submittedAssignments' => $submittedAssignments,
        ]);
    }
}

