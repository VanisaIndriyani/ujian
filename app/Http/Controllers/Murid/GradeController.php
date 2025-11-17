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

        return view('murid.grades.index', compact('grades'));
    }
}

