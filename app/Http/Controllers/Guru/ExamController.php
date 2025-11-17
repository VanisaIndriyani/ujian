<?php

namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Controller;
use App\Models\Exam;
use App\Models\Classroom;
use App\Models\ExamResult;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class ExamController extends Controller
{
    public function index(): View
    {
        $guru = Auth::user();
        $scopeParam = request('scope');
        $scope = $scopeParam === 'berkelas' ? 'berkelas' : 'semua';

        $query = Exam::with('subject')
            ->where('creator_id', $guru->id);

        if ($scope === 'berkelas') {
            $query->whereNotNull('classroom');
        }

        $exams = $query->orderByDesc('start_at')
            ->paginate(10)
            ->appends(['scope' => $scope]);

        // Ambil 10 peristiwa auto-finish terbaru untuk ujian buatan guru ini
        $autoFinishes = \App\Models\ExamResult::with(['exam.subject', 'student'])
            ->whereHas('exam', fn ($q) => $q->where('creator_id', $guru->id))
            ->where('status', 'auto_finished')
            ->orderByDesc('submitted_at')
            ->take(10)
            ->get();

        return view('guru.exams.index', compact('exams', 'scope', 'autoFinishes'));
    }

    public function create(): View
    {
        $subjects = Auth::user()->subjectsTeaching()->orderBy('name')->get();
        $classrooms = Classroom::orderBy('name')->pluck('name');

        return view('guru.exams.create', compact('subjects', 'classrooms'));
    }

    public function store(Request $request): RedirectResponse
    {
        $guru = Auth::user();

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

        abort_if(!$guru->subjectsTeaching()->where('subjects.id', $data['subject_id'])->exists(), 403);

        // Simpan berkas materi jika ada
        $materialPath = null;
        if ($request->hasFile('material_file')) {
            $materialPath = $request->file('material_file')->store('exams/materials', 'public');
        }

        Exam::create($data + [
            'creator_id' => $guru->id,
            'material_path' => $materialPath,
        ]);

        return redirect()->route('guru.exams.index')
            ->with('success', 'Ujian berhasil dibuat.');
    }

    public function edit(Exam $exam): View
    {
        $guru = Auth::user();
        abort_if($exam->creator_id !== $guru->id, 403);

        $subjects = Auth::user()->subjectsTeaching()->orderBy('name')->get();
        $classrooms = Classroom::orderBy('name')->pluck('name');

        return view('guru.exams.edit', compact('exam', 'subjects', 'classrooms'));
    }

    public function update(Request $request, Exam $exam): RedirectResponse
    {
        $guru = Auth::user();
        abort_if($exam->creator_id !== $guru->id, 403);

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

        abort_if(!$guru->subjectsTeaching()->where('subjects.id', $data['subject_id'])->exists(), 403);

        $materialPath = $exam->material_path;
        if ($request->hasFile('material_file')) {
            if (!empty($materialPath)) {
                \Illuminate\Support\Facades\Storage::disk('public')->delete($materialPath);
            }
            $materialPath = $request->file('material_file')->store('exams/materials', 'public');
        }

        $exam->update($data + [
            'material_path' => $materialPath,
        ]);

        return redirect()->route('guru.exams.index')
            ->with('success', 'Ujian berhasil diperbarui.');
    }

    public function destroy(Exam $exam): RedirectResponse
    {
        $guru = Auth::user();
        abort_if($exam->creator_id !== $guru->id, 403);

        if (!empty($exam->material_path)) {
            \Illuminate\Support\Facades\Storage::disk('public')->delete($exam->material_path);
        }

        $exam->delete();

        return redirect()->route('guru.exams.index')
            ->with('success', 'Ujian berhasil dihapus.');
    }

    /**
     * Tampilkan daftar jawaban ujian dari mahasiswa untuk ujian tertentu.
     */
    public function results(Exam $exam): View
    {
        $guru = Auth::user();
        abort_if($exam->creator_id !== $guru->id, 403);

        $results = $exam->results()
            ->with('student')
            ->orderByDesc('submitted_at')
            ->paginate(20);

        return view('guru.exams.results', compact('exam', 'results'));
    }

    /**
     * Simpan nilai ujian dan catatan oleh guru secara inline dari tabel hasil.
     */
    public function gradeResult(Request $request, Exam $exam, \App\Models\ExamResult $result): \Illuminate\Http\RedirectResponse
    {
        $guru = Auth::user();
        abort_if($exam->creator_id !== $guru->id, 403);
        abort_if($result->exam_id !== $exam->id, 404);

        $data = $request->validate([
            'score' => 'required|numeric|min:0|max:100',
            'notes' => 'nullable|string',
        ]);

        $result->update($data);

        return redirect()->route('guru.exams.results', $exam)
            ->with('success', 'Nilai ujian berhasil disimpan.');
    }

    /**
     * Unduh berkas jawaban yang diunggah mahasiswa.
     */
    public function downloadSubmission(Exam $exam, ExamResult $result)
    {
        $guru = Auth::user();
        abort_if($exam->creator_id !== $guru->id, 403);
        abort_if($result->exam_id !== $exam->id, 404);

        abort_if(empty($result->answer_path), 404);

        $filename = basename($result->answer_path);
        return Storage::disk('local')->download($result->answer_path, $filename);
    }
}

