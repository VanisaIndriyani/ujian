<?php

namespace App\Http\Controllers\Murid;

use App\Http\Controllers\Controller;
use App\Models\Exam;
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
        $murid = Auth::user();
        $subjectIds = $murid->subjects()->pluck('subjects.id');

        $exams = Exam::with(['subject', 'results' => function ($query) use ($murid) {
            $query->where('student_id', $murid->id);
        }])
            // Tampilkan ujian untuk kelas murid, atau ujian umum (classroom null) yang sesuai mata kuliah yang diikuti
            ->where(function ($query) use ($murid, $subjectIds) {
                $query->where('classroom', $murid->classroom)
                      ->orWhere(function ($q) use ($subjectIds) {
                          $q->whereNull('classroom')
                            ->whereIn('subject_id', $subjectIds);
                      });
            })
            ->orderBy('start_at')
            ->paginate(10);

        return view('murid.exams.index', compact('exams'));
    }

    public function show(Exam $exam): View
    {
        $murid = Auth::user();
        $enrolled = $murid->subjects()->where('subjects.id', $exam->subject_id)->exists();
        $classAllowed = !is_null($exam->classroom) && $exam->classroom === $murid->classroom;
        abort_if(!($enrolled || $classAllowed), 403);

        $result = $exam->results()->where('student_id', $murid->id)->first();

        return view('murid.exams.show', compact('exam', 'result'));
    }

    public function store(Request $request): RedirectResponse
    {
        $murid = Auth::user();

        $data = $request->validate([
            'exam_id' => 'required|exists:exams,id',
            'score' => 'required|numeric|min:0|max:100',
        ]);

        $exam = Exam::findOrFail($data['exam_id']);
        abort_if(!$murid->subjects()->where('subjects.id', $exam->subject_id)->exists(), 403);

        ExamResult::updateOrCreate(
            [
                'exam_id' => $exam->id,
                'student_id' => $murid->id,
            ],
            [
                'score' => $data['score'],
                'submitted_at' => now(),
            ]
        );

        return redirect()->route('murid.exams.index')
            ->with('success', 'Nilai ujian berhasil disimpan.');
    }

    /**
     * Unggah file jawaban (DOCX/PDF) untuk ujian berbasis dokumen.
     */
    public function submitFile(Request $request, Exam $exam): RedirectResponse
    {
        $murid = Auth::user();

        $enrolled = $murid->subjects()->where('subjects.id', $exam->subject_id)->exists();
        $classAllowed = !is_null($exam->classroom) && $exam->classroom === $murid->classroom;
        abort_if(!($enrolled || $classAllowed), 403);

        $validated = $request->validate([
            'answer' => 'required|file|mimes:doc,docx,pdf|max:20480',
        ]);

        $dir = "private/exam_submissions/{$exam->id}/{$murid->id}";
        $filename = now()->format('Ymd_His') . '_' . $validated['answer']->getClientOriginalName();
        $path = Storage::disk('local')->putFileAs($dir, $validated['answer'], $filename);

        ExamResult::updateOrCreate(
            [
                'exam_id' => $exam->id,
                'student_id' => $murid->id,
            ],
            [
                'answer_path' => $path,
                'status' => 'submitted',
                'notes' => 'Jawaban diunggah oleh murid.',
                'submitted_at' => now(),
            ]
        );

        return redirect()->route('murid.exams.show', $exam)
            ->with('success', 'Jawaban berhasil diunggah.');
    }

    /**
     * Simpan jawaban hasil ketikan di editor web (DOCX viewer + textarea).
     */
    public function submitText(Request $request, Exam $exam): RedirectResponse
    {
        $murid = Auth::user();

        $enrolled = $murid->subjects()->where('subjects.id', $exam->subject_id)->exists();
        $classAllowed = !is_null($exam->classroom) && $exam->classroom === $murid->classroom;
        abort_if(!($enrolled || $classAllowed), 403);

        $validated = $request->validate([
            'answer_text' => 'required|string|min:5',
        ]);

        ExamResult::updateOrCreate(
            [
                'exam_id' => $exam->id,
                'student_id' => $murid->id,
            ],
            [
                'answer_text' => $validated['answer_text'],
                'status' => 'submitted',
                'notes' => 'Jawaban diketik di editor web.',
                'submitted_at' => now(),
            ]
        );

        return redirect()->route('murid.exams.show', $exam)
            ->with('success', 'Jawaban berhasil disimpan.');
    }

    /**
     * Menandai ujian selesai otomatis (auto-finish) saat murid berpindah layar/tab.
     * Skor dikosongkan (null), dan status serta catatan disimpan untuk ditampilkan ke dosen.
     */
    public function autoFinish(Request $request, Exam $exam)
    {
        $murid = Auth::user();

        // Izinkan jika murid terdaftar di mata kuliah atau ujian berkelas sesuai kelas murid
        $enrolled = $murid->subjects()->where('subjects.id', $exam->subject_id)->exists();
        $classAllowed = !is_null($exam->classroom) && $exam->classroom === $murid->classroom;
        abort_if(!($enrolled || $classAllowed), 403);

        ExamResult::updateOrCreate(
            [
                'exam_id' => $exam->id,
                'student_id' => $murid->id,
            ],
            [
                'score' => null,
                'status' => 'auto_finished',
                'notes' => 'Ujian selesai otomatis: berpindah layar/tab saat ujian.',
                'submitted_at' => now(),
            ]
        );

        return response()->json(['ok' => true]);
    }
}

