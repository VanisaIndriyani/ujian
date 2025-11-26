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
            'semester' => 'required|integer|min:1|max:8',
            'classroom' => 'required|exists:classrooms,name',
            'type' => 'nullable|in:UTS,UAS',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'question_url' => 'nullable|url',
            'material_file' => 'nullable|file|mimes:doc,docx,pdf|max:20480',
            'start_at' => 'nullable|date',
            'end_at' => 'nullable|date|after_or_equal:start_at',
            'enable_multiple_choice' => 'nullable|boolean',
            'questions' => 'nullable|array',
            'questions.*.question' => 'required_with:questions|string',
            'questions.*.options.A' => 'required_with:questions|string',
            'questions.*.options.B' => 'required_with:questions|string',
            'questions.*.options.C' => 'required_with:questions|string',
            'questions.*.options.D' => 'required_with:questions|string',
            'answer_key' => 'nullable|array',
            'answer_key.*' => 'required_with:answer_key|in:A,B,C,D',
        ]);

        abort_if(!$guru->subjectsTeaching()->where('subjects.id', $data['subject_id'])->exists(), 403);

        // Simpan berkas materi jika ada
        $materialPath = null;
        if ($request->hasFile('material_file')) {
            $materialPath = $request->file('material_file')->store('exams/materials', 'public');
        }

        // Proses soal pilihan ganda
        $questionsJson = null;
        $answerKeyJson = null;
        if ($request->has('enable_multiple_choice') && $request->has('questions') && !empty($request->input('questions'))) {
            $questions = [];
            $answerKey = [];
            foreach ($request->input('questions') as $index => $question) {
                if (!empty($question['question']) && !empty($question['options'])) {
                    $questions[] = [
                        'question' => $question['question'],
                        'options' => $question['options'],
                    ];
                    if (isset($request->input('answer_key')[$index])) {
                        $answerKey[] = $request->input('answer_key')[$index];
                    }
                }
            }
            if (!empty($questions)) {
                $questionsJson = $questions;
                $answerKeyJson = $answerKey;
            }
        }

        Exam::create($data + [
            'creator_id' => $guru->id,
            'material_path' => $materialPath,
            'questions_json' => $questionsJson,
            'answer_key_json' => $answerKeyJson,
        ]);

        return redirect()->route('guru.exams.index')
            ->with('success', 'Ujian berhasil dibuat.');
    }

    public function edit(Exam $exam): View
    {
        $guru = Auth::user();
        // Admin dan Guru bisa akses semua exams
        if (!in_array($guru->role, ['admin', 'guru'])) {
            abort(403, 'Anda tidak memiliki izin untuk mengakses halaman ini.');
        }

        $subjects = Auth::user()->subjectsTeaching()->orderBy('name')->get();
        $classrooms = Classroom::orderBy('name')->pluck('name');

        return view('guru.exams.edit', compact('exam', 'subjects', 'classrooms'));
    }

    public function update(Request $request, Exam $exam): RedirectResponse
    {
        $guru = Auth::user();
        // Admin dan Guru bisa akses semua exams
        if (!in_array($guru->role, ['admin', 'guru'])) {
            abort(403, 'Anda tidak memiliki izin untuk mengakses halaman ini.');
        }

        $data = $request->validate([
            'subject_id' => 'required|exists:subjects,id',
            'semester' => 'required|integer|min:1|max:8',
            'classroom' => 'required|exists:classrooms,name',
            'type' => 'nullable|in:UTS,UAS',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'question_url' => 'nullable|url',
            'material_file' => 'nullable|file|mimes:doc,docx,pdf|max:20480',
            'start_at' => 'nullable|date',
            'end_at' => 'nullable|date|after_or_equal:start_at',
            'enable_multiple_choice' => 'nullable|boolean',
            'questions' => 'nullable|array',
            'questions.*.question' => 'required_with:questions|string',
            'questions.*.options.A' => 'required_with:questions|string',
            'questions.*.options.B' => 'required_with:questions|string',
            'questions.*.options.C' => 'required_with:questions|string',
            'questions.*.options.D' => 'required_with:questions|string',
            'answer_key' => 'nullable|array',
            'answer_key.*' => 'required_with:answer_key|in:A,B,C,D',
        ]);

        // Jika guru (bukan admin), pastikan subject yang dipilih adalah subject yang diajarkan oleh guru tersebut
        if ($guru->role === 'guru') {
            abort_if(!$guru->subjectsTeaching()->where('subjects.id', $data['subject_id'])->exists(), 403, 'Anda tidak memiliki izin untuk mengubah mata kuliah ini.');
        }

        $materialPath = $exam->material_path;
        if ($request->hasFile('material_file')) {
            if (!empty($materialPath)) {
                \Illuminate\Support\Facades\Storage::disk('public')->delete($materialPath);
            }
            $materialPath = $request->file('material_file')->store('exams/materials', 'public');
        }

        // Proses soal pilihan ganda
        $questionsJson = null;
        $answerKeyJson = null;
        if ($request->has('enable_multiple_choice') && $request->has('questions') && !empty($request->input('questions'))) {
            $questions = [];
            $answerKey = [];
            foreach ($request->input('questions') as $index => $question) {
                if (!empty($question['question']) && !empty($question['options'])) {
                    $questions[] = [
                        'question' => $question['question'],
                        'options' => $question['options'],
                    ];
                    if (isset($request->input('answer_key')[$index])) {
                        $answerKey[] = $request->input('answer_key')[$index];
                    }
                }
            }
            if (!empty($questions)) {
                $questionsJson = $questions;
                $answerKeyJson = $answerKey;
            }
        }

        $exam->update($data + [
            'material_path' => $materialPath,
            'questions_json' => $questionsJson,
            'answer_key_json' => $answerKeyJson,
        ]);

        return redirect()->route('guru.exams.index')
            ->with('success', 'Ujian berhasil diperbarui.');
    }

    public function destroy(Exam $exam): RedirectResponse
    {
        $guru = Auth::user();
        // Admin dan Guru bisa akses semua exams
        if (!in_array($guru->role, ['admin', 'guru'])) {
            abort(403, 'Anda tidak memiliki izin untuk mengakses halaman ini.');
        }

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
        // Admin dan Guru bisa akses semua exams results
        if (!in_array($guru->role, ['admin', 'guru'])) {
            abort(403, 'Anda tidak memiliki izin untuk mengakses halaman ini.');
        }

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

