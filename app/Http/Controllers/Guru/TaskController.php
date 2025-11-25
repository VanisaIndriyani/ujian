<?php

namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Controller;
use App\Models\Assignment;
use App\Models\AssignmentSubmission;
use App\Models\Subject;
use App\Models\Classroom;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class TaskController extends Controller
{
    public function index(): View
    {
        $guru = Auth::user();

        $assignments = Assignment::with('subject')
            ->withCount('submissions')
            ->where('guru_id', $guru->id)
            ->orderByDesc('created_at')
            ->paginate(10);

        return view('guru.tasks.index', compact('assignments'));
    }

    public function create(): View
    {
        $subjects = Auth::user()->subjectsTeaching()->orderBy('name')->get();
        $classrooms = Classroom::orderBy('name')->pluck('name');

        return view('guru.tasks.create', compact('subjects', 'classrooms'));
    }

    public function store(Request $request): RedirectResponse
    {
        $guru = Auth::user();

        $data = $request->validate([
            'subject_id' => 'required|exists:subjects,id',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'due_at' => 'nullable|date',
            'classroom' => 'nullable|exists:classrooms,name',
        ]);

        Assignment::create($data + [
            'guru_id' => $guru->id,
        ]);

        return redirect()->route('guru.tasks.index')
            ->with('success', 'Tugas berhasil dibuat.');
    }

    public function show(Assignment $task): View
    {
        $this->authorizeTask($task);

        $task->load([
            'subject',
            'submissions' => fn ($query) => $query->with('student')->orderByDesc('submitted_at')->orderByDesc('created_at'),
        ]);

        return view('guru.tasks.show', [
            'task' => $task,
            'submissions' => $task->submissions,
        ]);
    }

    public function edit(Assignment $task): View
    {
        $this->authorizeTask($task);

        $subjects = Auth::user()->subjectsTeaching()->orderBy('name')->get();
        $classrooms = Classroom::orderBy('name')->pluck('name');

        return view('guru.tasks.edit', ['task' => $task, 'subjects' => $subjects, 'classrooms' => $classrooms]);
    }

    public function update(Request $request, Assignment $task): RedirectResponse
    {
        $this->authorizeTask($task);

        $data = $request->validate([
            'subject_id' => 'required|exists:subjects,id',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'due_at' => 'nullable|date',
            'classroom' => 'nullable|exists:classrooms,name',
        ]);

        $task->update($data);

        return redirect()->route('guru.tasks.index')
            ->with('success', 'Tugas berhasil diperbarui.');
    }

    public function destroy(Assignment $task): RedirectResponse
    {
        $this->authorizeTask($task);

        $task->delete();

        return redirect()->route('guru.tasks.index')
            ->with('success', 'Tugas berhasil dihapus.');
    }

    public function gradeSubmission(Request $request, Assignment $task, AssignmentSubmission $submission): RedirectResponse
    {
        $this->authorizeTask($task);

        abort_if($submission->assignment_id !== $task->id, 404);

        $data = $request->validate([
            'score' => 'required|numeric|min:0|max:100',
            'feedback' => 'nullable|string',
        ]);

        $submission->update($data);

        return redirect()
            ->route('guru.tasks.show', $task)
            ->with('success', 'Nilai tugas berhasil disimpan.');
    }

    protected function authorizeTask(Assignment $task): void
    {
        $user = Auth::user();
        
        // Admin bisa akses semua tasks
        if ($user->role === 'admin') {
            return;
        }
        
        // Guru yang membuat task bisa akses
        if ($task->guru_id === $user->id) {
            return;
        }
        
        // Load subject jika belum di-load
        if (!$task->relationLoaded('subject')) {
            $task->load('subject');
        }
        
        // Guru yang mengajar subject dari task tersebut juga bisa akses
        if ($task->subject && $task->subject->guru_id === $user->id) {
            return;
        }
        
        // Jika tidak memenuhi kondisi di atas, tolak akses
        abort(403, 'Anda tidak memiliki izin untuk mengakses tugas ini.');
    }
}

