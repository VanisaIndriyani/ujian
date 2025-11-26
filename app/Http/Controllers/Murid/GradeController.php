<?php

namespace App\Http\Controllers\Murid;

use App\Http\Controllers\Controller;
use App\Models\SemesterGrade;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class GradeController extends Controller
{
    public function index(): View
    {
        $murid = Auth::user();

        $grades = SemesterGrade::with('subject')
            ->where('student_id', $murid->id)
            ->orderByDesc('created_at')
            ->paginate(10);

        // Ambil nilai UAS dari exam_results untuk setiap grade
        $examResults = \App\Models\ExamResult::with('exam')
            ->where('student_id', $murid->id)
            ->whereNotNull('score')
            ->whereHas('exam', function($query) {
                $query->where('type', 'UAS');
            })
            ->get()
            ->groupBy(function($result) {
                return $result->exam->subject_id;
            });

        return view('murid.grades.index', compact('grades', 'examResults'));
    }
}

