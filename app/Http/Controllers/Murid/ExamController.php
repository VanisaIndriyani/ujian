<?php

namespace App\Http\Controllers\Murid;

use App\Http\Controllers\Controller;
use App\Models\Exam;
use App\Models\ExamResult;
use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\IOFactory;
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

    /**
     * Menandai ujian selesai secara manual (finish) saat murid klik tombol selesai.
     * Simpan jawaban terakhir dan tandai status sebagai 'finished'.
     */
    public function finish(Request $request, Exam $exam)
    {
        $murid = Auth::user();

        // Izinkan jika murid terdaftar di mata kuliah atau ujian berkelas sesuai kelas murid
        $enrolled = $murid->subjects()->where('subjects.id', $exam->subject_id)->exists();
        $classAllowed = !is_null($exam->classroom) && $exam->classroom === $murid->classroom;
        abort_if(!($enrolled || $classAllowed), 403);

        // Cek apakah ujian sudah selesai sebelumnya
        $existingResult = ExamResult::where('exam_id', $exam->id)
            ->where('student_id', $murid->id)
            ->first();

        if ($existingResult && in_array($existingResult->status, ['submitted', 'finished', 'auto_finished'])) {
            return response()->json(['ok' => true, 'message' => 'Ujian sudah selesai.']);
        }

        $data = $request->validate([
            'answer_text' => 'nullable|string',
            'answers' => 'nullable|array',
        ]);

        $updateData = [
            'answer_text' => $data['answer_text'] ?? $existingResult->answer_text ?? null,
            'status' => 'finished',
            'notes' => 'Ujian selesai oleh murid.',
            'submitted_at' => now(),
        ];

        // Auto-grading untuk soal pilihan ganda
        if (!empty($exam->questions_json) && !empty($exam->answer_key_json) && !empty($data['answers'])) {
            $questions = $exam->questions_json;
            $answerKey = $exam->answer_key_json;
            $studentAnswers = $data['answers'];
            
            $correct = 0;
            $total = count($questions);
            
            foreach ($questions as $index => $question) {
                $correctAnswer = $answerKey[$index] ?? null;
                $studentAnswer = $studentAnswers[$index] ?? null;
                
                if ($correctAnswer && $studentAnswer && $correctAnswer === $studentAnswer) {
                    $correct++;
                }
            }
            
            $score = $total > 0 ? round(($correct / $total) * 100, 2) : 0;
            
            $updateData['answers_json'] = $studentAnswers;
            $updateData['score'] = $score;
            $updateData['notes'] = "Ujian selesai. Skor: {$correct}/{$total} ({$score})";
        }

        ExamResult::updateOrCreate(
            [
                'exam_id' => $exam->id,
                'student_id' => $murid->id,
            ],
            $updateData
        );

        return response()->json(['ok' => true, 'message' => 'Ujian berhasil diselesaikan.', 'score' => $updateData['score'] ?? null]);
    }

    /**
     * Heartbeat ujian: menandai sesi aktif dan mencatat pelanggaran/remaining time.
     * Disimpan di ExamResult sebagai status "in_progress" dan catatan singkat.
     */
    public function heartbeat(Request $request, Exam $exam)
    {
        $murid = Auth::user();

        // Validasi akses murid ke ujian
        $enrolled = $murid->subjects()->where('subjects.id', $exam->subject_id)->exists();
        $classAllowed = !is_null($exam->classroom) && $exam->classroom === $murid->classroom;
        abort_if(!($enrolled || $classAllowed), 403);

        $data = $request->validate([
            'violations' => 'nullable|integer|min:0',
            'remaining_ms' => 'nullable|integer|min:0',
        ]);

        $notes = sprintf(
            'Heartbeat: violations=%d, remaining_ms=%d, at=%s',
            (int) ($data['violations'] ?? 0),
            (int) ($data['remaining_ms'] ?? 0),
            now()->toDateTimeString()
        );

        ExamResult::updateOrCreate(
            [
                'exam_id' => $exam->id,
                'student_id' => $murid->id,
            ],
            [
                'status' => 'in_progress',
                'notes' => $notes,
            ]
        );

        return response()->json(['ok' => true]);
    }

    /**
     * Ekspor jawaban teks menjadi file DOCX untuk diunduh murid.
     * Jika `answer_text` tidak dikirim, akan memakai jawaban yang tersimpan pada ExamResult.
     */
    public function exportDocx(Request $request, Exam $exam)
    {
        $murid = Auth::user();
        $enrolled = $murid->subjects()->where('subjects.id', $exam->subject_id)->exists();
        $classAllowed = !is_null($exam->classroom) && $exam->classroom === $murid->classroom;
        abort_if(!($enrolled || $classAllowed), 403);

        $validated = $request->validate([
            'answer_text' => 'nullable|string|min:1',
        ]);

        $result = ExamResult::where('exam_id', $exam->id)
            ->where('student_id', $murid->id)
            ->first();

        $answerText = $validated['answer_text'] ?? ($result->answer_text ?? '');
        if (trim($answerText) === '') {
            return redirect()->back()->with('error', 'Tidak ada jawaban untuk diekspor.');
        }

        $phpWord = new PhpWord();
        $section = $phpWord->addSection();
        $section->addText('Jawaban Ujian', [
            'bold' => true,
            'size' => 14,
        ]);
        $section->addText(sprintf('Matakuliah: %s', $exam->subject?->name ?? '-'));
        $section->addText(sprintf('Ujian: %s', $exam->title));
        $section->addTextBreak(1);
        $section->addText($answerText, ['size' => 12]);

        $fileName = 'Jawaban_' . ($exam->title ? preg_replace('/[^A-Za-z0-9_\-]/', '_', $exam->title) : 'Ujian') . '_' . now()->format('Ymd_His') . '.docx';

        $tempFile = tempnam(sys_get_temp_dir(), 'docx');
        $writer = IOFactory::createWriter($phpWord, 'Word2007');
        $writer->save($tempFile);

        return response()->download($tempFile, $fileName, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        ])->deleteFileAfterSend(true);
    }
}

