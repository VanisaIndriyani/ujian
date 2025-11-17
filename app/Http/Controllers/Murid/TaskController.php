<?php

namespace App\Http\Controllers\Murid;

use App\Http\Controllers\Controller;
use App\Models\Assignment;
use App\Models\AssignmentSubmission;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class TaskController extends Controller
{
    public function index(Request $request): View
    {
        $murid = Auth::user();
        $subjectIds = $murid->subjects()->pluck('subjects.id');
        $status = $request->query('status'); // nilai: 'sudah' | 'belum' | null

        $assignments = Assignment::with(['subject', 'submissions' => function ($query) use ($murid) {
            $query->where('student_id', $murid->id);
        }])
            ->where(function ($query) use ($murid, $subjectIds) {
                // Tampilkan tugas untuk kelas murid, atau tugas semua kelas yang sesuai mata kuliah yang diikuti
                $query->where('classroom', $murid->classroom)
                      ->orWhere(function ($q) use ($subjectIds) {
                          $q->whereNull('classroom')
                            ->whereIn('subject_id', $subjectIds);
                      });
            })
            ->when($status === 'sudah', function ($q) use ($murid) {
                $q->whereHas('submissions', function ($sub) use ($murid) {
                    $sub->where('student_id', $murid->id);
                });
            })
            ->when($status === 'belum', function ($q) use ($murid) {
                $q->whereDoesntHave('submissions', function ($sub) use ($murid) {
                    $sub->where('student_id', $murid->id);
                });
            })
            ->orderBy('due_at')
            ->paginate(10)
            ->withQueryString();

        return view('murid.tasks.index', compact('assignments', 'status'));
    }

    public function create(): View
    {
        $murid = Auth::user();
        $subjectIds = $murid->subjects()->pluck('subjects.id');
        // Jika ada assignment_id di query, validasi akses dan tenggat sebelum menampilkan form
        if (request('assignment_id')) {
            $assignment = Assignment::find(request('assignment_id'));
            if ($assignment) {
                $enrolled = $murid->subjects()->where('subjects.id', $assignment->subject_id)->exists();
                $classAllowed = !is_null($assignment->classroom) && $assignment->classroom === $murid->classroom;
                if (!($enrolled || $classAllowed)) {
                    abort(403);
                }
                if ($assignment->due_at && now()->greaterThan($assignment->due_at)) {
                    return redirect()->route('murid.tasks.index')
                        ->with('error', 'Pengumpulan ditutup: melewati tenggat waktu.');
                }
            }
        }
        $assignments = Assignment::with('subject')
            ->where(function ($query) use ($murid, $subjectIds) {
                $query->where('classroom', $murid->classroom)
                      ->orWhere(function ($q) use ($subjectIds) {
                          $q->whereNull('classroom')
                            ->whereIn('subject_id', $subjectIds);
                      });
            })
            ->orderBy('due_at')
            ->get();

        return view('murid.tasks.create', compact('assignments'));
    }

    public function store(Request $request): RedirectResponse
    {
        $murid = Auth::user();

        $data = $request->validate([
            'assignment_id' => 'required|exists:assignments,id',
            'answer' => 'nullable|string',
            'file' => 'nullable|file|max:2048',
        ]);

        $assignment = Assignment::findOrFail($data['assignment_id']);
        $enrolled = $murid->subjects()->where('subjects.id', $assignment->subject_id)->exists();
        $classAllowed = !is_null($assignment->classroom) && $assignment->classroom === $murid->classroom;
        abort_unless($enrolled || $classAllowed, 403);

        // Blokir pengumpulan jika sudah melewati tenggat waktu
        if ($assignment->due_at && now()->greaterThan($assignment->due_at)) {
            return redirect()->route('murid.tasks.index')
                ->with('error', 'Pengumpulan ditutup: melewati tenggat waktu.');
        }

        $submission = AssignmentSubmission::firstOrNew([
            'assignment_id' => $assignment->id,
            'student_id' => $murid->id,
        ]);

        if ($request->hasFile('file')) {
            if ($submission->file_path) {
                Storage::disk('public')->delete($submission->file_path);
            }

            $submission->file_path = $request->file('file')->store('submissions', 'public');
        }

        $submission->answer = $data['answer'] ?? $submission->answer;
        $submission->submitted_at = now();
        $submission->save();

        return redirect()->route('murid.tasks.index')
            ->with('success', 'Tugas berhasil dikumpulkan.');
    }
}

