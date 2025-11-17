<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Exam;
use App\Models\Classroom;
use App\Models\ExamResult;
use Illuminate\Support\Facades\Storage;
use App\Models\Subject;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ExamController extends Controller
{
    public function index(): View
    {
        $exams = Exam::with('subject')->orderByDesc('start_at')->paginate(10);

        return view('admin.exams.index', compact('exams'));
    }

    public function create(): View
    {
        $subjects = Subject::orderBy('name')->get();
        $classrooms = Classroom::orderBy('name')->pluck('name');

        return view('admin.exams.create', compact('subjects', 'classrooms'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'subject_id' => 'required|exists:subjects,id',
            'classroom' => 'required|exists:classrooms,name',
            'type' => 'nullable|in:UTS,UAS',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'question_url' => 'nullable|url',
            'material_file' => 'nullable|file|mimes:doc,docx,pdf|max:20480',
            'start_at' => 'nullable|date',
            'end_at' => 'nullable|date|after_or_equal:start_at',
        ]);

        $materialPath = null;
        if ($request->hasFile('material_file')) {
            $materialPath = $request->file('material_file')->store('exams/materials', 'public');
        }

        Exam::create([
            'subject_id' => $data['subject_id'],
            'creator_id' => $request->user()->id,
            'classroom' => $data['classroom'],
            'type' => $data['type'] ?? null,
            'title' => $data['title'],
            'description' => $data['description'] ?? null,
            'question_url' => $data['question_url'] ?? null,
            'material_path' => $materialPath,
            'start_at' => $data['start_at'] ?? null,
            'end_at' => $data['end_at'] ?? null,
        ]);

        return redirect()->route('admin.exams.index')
            ->with('success', 'Data ujian berhasil dibuat.');
    }

    public function edit(Exam $exam): View
    {
        $subjects = Subject::orderBy('name')->get();
        $classrooms = Classroom::orderBy('name')->pluck('name');

        return view('admin.exams.edit', compact('exam', 'subjects', 'classrooms'));
    }

    public function update(Request $request, Exam $exam): RedirectResponse
    {
        $data = $request->validate([
            'subject_id' => 'required|exists:subjects,id',
            'classroom' => 'required|exists:classrooms,name',
            'type' => 'nullable|in:UTS,UAS',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'question_url' => 'nullable|url',
            'material_file' => 'nullable|file|mimes:doc,docx,pdf|max:20480',
            'start_at' => 'nullable|date',
            'end_at' => 'nullable|date|after_or_equal:start_at',
        ]);

        $materialPath = $exam->material_path;
        if ($request->hasFile('material_file')) {
            $newPath = $request->file('material_file')->store('exams/materials', 'public');
            if ($materialPath) {
                Storage::disk('public')->delete($materialPath);
            }
            $materialPath = $newPath;
        }

        $exam->update([
            'subject_id' => $data['subject_id'],
            'classroom' => $data['classroom'],
            'type' => $data['type'] ?? null,
            'title' => $data['title'],
            'description' => $data['description'] ?? null,
            'question_url' => $data['question_url'] ?? null,
            'material_path' => $materialPath,
            'start_at' => $data['start_at'] ?? null,
            'end_at' => $data['end_at'] ?? null,
        ]);

        return redirect()->route('admin.exams.index')
            ->with('success', 'Data ujian berhasil diperbarui.');
    }

    public function destroy(Exam $exam): RedirectResponse
    {
        $exam->delete();

        return redirect()->route('admin.exams.index')
            ->with('success', 'Data ujian berhasil dihapus.');
    }

    /**
     * Tampilkan daftar jawaban ujian dari mahasiswa untuk ujian tertentu (admin melihat semua).
     */
    public function results(Exam $exam): View
    {
        $results = $exam->results()
            ->with('student')
            ->orderByDesc('submitted_at')
            ->paginate(20);

        return view('admin.exams.results', compact('exam', 'results'));
    }

    /**
     * Admin memperbarui nilai dan catatan untuk jawaban ujian.
     */
    public function gradeResult(Request $request, Exam $exam, ExamResult $result): RedirectResponse
    {
        abort_if($result->exam_id !== $exam->id, 404);

        $data = $request->validate([
            'score' => 'required|numeric|min:0|max:100',
            'notes' => 'nullable|string',
        ]);

        $result->update($data);

        return redirect()->route('admin.exams.results', $exam)
            ->with('success', 'Nilai ujian berhasil disimpan.');
    }

    /**
     * Admin mengunduh berkas jawaban yang diunggah mahasiswa.
     */
    public function downloadSubmission(Exam $exam, ExamResult $result)
    {
        abort_if($result->exam_id !== $exam->id, 404);
        abort_if(empty($result->answer_path), 404);

        $filename = basename($result->answer_path);
        return Storage::disk('local')->download($result->answer_path, $filename);
    }
}

