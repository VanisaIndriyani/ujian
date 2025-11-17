<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\AssignmentSubmission;
use App\Models\ExamResult;
use App\Models\Classroom;
use App\Models\SemesterGrade;
use App\Models\Subject;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class GradeController extends Controller
{
    public function index(Request $request): View
    {
        $subjectId = $request->query('subject_id');
        $q = strtolower(trim((string) $request->query('q')));

        // Daftar siswa (semua murid) dengan pencarian nama/kelas
        $students = User::query()
            ->where('role', 'murid')
            ->when(!empty($q), fn ($qq) => $qq->where(function ($w) use ($q) {
                $w->where('name', 'like', "%$q%")
                  ->orWhere('classroom', 'like', "%$q%");
            }))
            ->orderBy('name')
            ->paginate(25)
            ->withQueryString();

        $subjects = Subject::orderBy('name')->get();

        // Bobot default dipakai untuk link export per siswa
        $weights = [
            'w_absen' => 10,
            'w_praktikum' => 10,
            'w_tugas' => 20,
            'w_uts' => 30,
            'w_uas' => 30,
        ];

        return view('admin.grades.index', compact('students', 'subjects', 'subjectId', 'q', 'weights'));
    }

    public function create(): View
    {
        $students = User::where('role', 'murid')->orderBy('name')->get();
        $subjects = Subject::orderBy('name')->get();

        return view('admin.grades.create', compact('students', 'subjects'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validateData($request);

        SemesterGrade::create($data + [
            'recorded_by' => $request->user()->id,
        ]);

        return redirect()->route('admin.grades.index')
            ->with('success', 'Nilai semester berhasil disimpan.');
    }

    public function edit(SemesterGrade $grade): View
    {
        $students = User::where('role', 'murid')->orderBy('name')->get();
        $subjects = Subject::orderBy('name')->get();

        return view('admin.grades.edit', compact('grade', 'students', 'subjects'));
    }

    public function update(Request $request, SemesterGrade $grade): RedirectResponse
    {
        $data = $this->validateData($request);

        $grade->update($data + [
            'recorded_by' => $request->user()->id,
        ]);

        return redirect()->route('admin.grades.index')
            ->with('success', 'Nilai semester berhasil diperbarui.');
    }

    public function destroy(SemesterGrade $grade): RedirectResponse
    {
        $grade->delete();

        return redirect()->route('admin.grades.index')
            ->with('success', 'Nilai semester berhasil dihapus.');
    }

    private function validateData(Request $request): array
    {
        return $request->validate([
            'student_id' => 'required|exists:users,id',
            'subject_id' => 'required|exists:subjects,id',
            'score' => 'required|numeric|min:0|max:100',
            'semester' => 'required|string|max:50',
            'notes' => 'nullable|string',
        ]);
    }

    private function gradeLetter(float $avg): string
    {
        if ($avg >= 85) return 'A';
        if ($avg >= 75) return 'B';
        if ($avg >= 65) return 'C';
        if ($avg >= 55) return 'D';
        return 'E';
    }

    private function categoryDescription(float $avg): string
    {
        if ($avg >= 85) return 'Baik Sekali';
        if ($avg >= 70) return 'Baik';
        if ($avg >= 60) return 'Cukup';
        return 'Kurang';
    }

    public function showStudent(Request $request, User $student): View
    {
        $subjects = Subject::orderBy('name')->get();
        $subjectId = (int) $request->query('subject_id', $subjects->first()->id ?? 0);

        $wAbsen = (float) $request->query('w_absen', 10);
        $wPraktikum = (float) $request->query('w_praktikum', 10);
        $wTugas = (float) $request->query('w_tugas', 20);
        $wUts = (float) $request->query('w_uts', 30);
        $wUas = (float) $request->query('w_uas', 30);

        $attendanceScore = null;
        if ($subjectId) {
            $attTotal = Attendance::where('subject_id', $subjectId)
                ->where('student_id', $student->id)
                ->count();
            if ($attTotal > 0) {
                $hadir = Attendance::where('subject_id', $subjectId)
                    ->where('student_id', $student->id)
                    ->where('status', 'hadir')
                    ->count();
                $attendanceScore = round(($hadir / $attTotal) * 100, 2);
            }
        }

        $latestUts = ExamResult::with(['exam'])
            ->whereHas('exam', fn ($q) => $q->where('subject_id', $subjectId)->where('type', 'UTS'))
            ->where('student_id', $student->id)
            ->orderByDesc('submitted_at')
            ->orderByDesc('created_at')
            ->first();
        $latestUas = ExamResult::with(['exam'])
            ->whereHas('exam', fn ($q) => $q->where('subject_id', $subjectId)->where('type', 'UAS'))
            ->where('student_id', $student->id)
            ->orderByDesc('submitted_at')
            ->orderByDesc('created_at')
            ->first();

        $latestSubmissions = AssignmentSubmission::with(['assignment'])
            ->whereHas('assignment', fn ($q) => $q->where('subject_id', $subjectId))
            ->where('student_id', $student->id)
            ->whereNotNull('score')
            ->orderByDesc('submitted_at')
            ->get();

        $tugasScore = null; $praktikumScore = null;
        foreach ($latestSubmissions as $sub) {
            $title = strtolower((string)($sub->assignment?->title ?? ''));
            $desc = strtolower((string)($sub->assignment?->description ?? ''));
            $isPraktikum = str_contains($title, 'praktikum') || str_contains($desc, 'praktikum');
            if ($isPraktikum && is_null($praktikumScore)) {
                $praktikumScore = $sub->score;
            } elseif (is_null($tugasScore)) {
                $tugasScore = $sub->score;
            }
        }

        $utsScore = $latestUts?->score;
        $uasScore = $latestUas?->score;

        $saved = null; $savedConfig = [];
        if ($subjectId) {
            $saved = SemesterGrade::where('student_id', $student->id)
                ->where('subject_id', $subjectId)
                ->orderByDesc('created_at')
                ->first();
            if ($saved && !empty($saved->notes)) {
                try { $savedConfig = json_decode($saved->notes, true) ?: []; } catch (\Throwable $e) { $savedConfig = []; }
            }
        }

        $attendanceScore = $savedConfig['attendance_score'] ?? $attendanceScore;
        $utsScore = $savedConfig['uts_score'] ?? $utsScore;
        $uasScore = $savedConfig['uas_score'] ?? $uasScore;
        $tugasScore = $savedConfig['tugas_score'] ?? $tugasScore;
        $praktikumScore = $savedConfig['praktikum_score'] ?? $praktikumScore;
        $wAbsen = $savedConfig['w_absen'] ?? $wAbsen;
        $wPraktikum = $savedConfig['w_praktikum'] ?? $wPraktikum;
        $wTugas = $savedConfig['w_tugas'] ?? $wTugas;
        $wUts = $savedConfig['w_uts'] ?? $wUts;
        $wUas = $savedConfig['w_uas'] ?? $wUas;

        $pairs = [
            ['score' => $attendanceScore, 'weight' => $wAbsen],
            ['score' => $praktikumScore, 'weight' => $wPraktikum],
            ['score' => $tugasScore, 'weight' => $wTugas],
            ['score' => $utsScore, 'weight' => $wUts],
            ['score' => $uasScore, 'weight' => $wUas],
        ];
        $sumWeights = 0.0; $sum = 0.0;
        foreach ($pairs as $p) {
            if (!is_null($p['score']) && $p['weight'] > 0) {
                $sumWeights += (float) $p['weight'];
                $sum += (float) $p['score'] * (float) $p['weight'];
            }
        }
        $finalScore = $sumWeights > 0 ? round($sum / $sumWeights, 2) : null;
        $predikat = is_null($finalScore) ? '—' : $this->gradeLetter($finalScore);
        $keterangan = is_null($finalScore) ? '—' : $this->categoryDescription($finalScore);

        return view('admin.grades.show', [
            'student' => $student,
            'subjects' => $subjects,
            'subjectId' => $subjectId,
            'weights' => compact('wAbsen', 'wPraktikum', 'wTugas', 'wUts', 'wUas'),
            'components' => [
                'attendance' => $attendanceScore,
                'praktikum' => $praktikumScore,
                'tugas' => $tugasScore,
                'uts' => $utsScore,
                'uas' => $uasScore,
            ],
            'finalScore' => $finalScore,
            'predikat' => $predikat,
            'keterangan' => $keterangan,
        ]);
    }

    public function saveStudent(Request $request, User $student)
    {
        $data = $request->validate([
            'subject_id' => ['required', 'integer', 'exists:subjects,id'],
            'attendance_score' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'praktikum_score' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'tugas_score' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'uts_score' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'uas_score' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'w_absen' => ['required', 'numeric', 'min:0', 'max:100'],
            'w_praktikum' => ['required', 'numeric', 'min:0', 'max:100'],
            'w_tugas' => ['required', 'numeric', 'min:0', 'max:100'],
            'w_uts' => ['required', 'numeric', 'min:0', 'max:100'],
            'w_uas' => ['required', 'numeric', 'min:0', 'max:100'],
        ]);

        $pairs = [
            ['score' => $data['attendance_score'] ?? null, 'weight' => (float) $data['w_absen']],
            ['score' => $data['praktikum_score'] ?? null, 'weight' => (float) $data['w_praktikum']],
            ['score' => $data['tugas_score'] ?? null, 'weight' => (float) $data['w_tugas']],
            ['score' => $data['uts_score'] ?? null, 'weight' => (float) $data['w_uts']],
            ['score' => $data['uas_score'] ?? null, 'weight' => (float) $data['w_uas']],
        ];
        $sumWeights = 0.0; $sum = 0.0;
        foreach ($pairs as $p) {
            if (!is_null($p['score']) && $p['weight'] > 0) {
                $sumWeights += (float) $p['weight'];
                $sum += (float) $p['score'] * (float) $p['weight'];
            }
        }
        $finalScore = $sumWeights > 0 ? round($sum / $sumWeights, 2) : null;
        $predikat = is_null($finalScore) ? null : $this->gradeLetter($finalScore);
        $category = is_null($finalScore) ? null : $this->categoryDescription($finalScore);

        $sg = SemesterGrade::firstOrNew([
            'student_id' => $student->id,
            'subject_id' => (int) $data['subject_id'],
            'semester' => 'Genap',
        ]);
        $sg->score = $finalScore;
        $sg->recorded_by = Auth::id();
        $sg->notes = json_encode([
            'attendance_score' => $data['attendance_score'] ?? null,
            'praktikum_score' => $data['praktikum_score'] ?? null,
            'tugas_score' => $data['tugas_score'] ?? null,
            'uts_score' => $data['uts_score'] ?? null,
            'uas_score' => $data['uas_score'] ?? null,
            'w_absen' => (float) $data['w_absen'],
            'w_praktikum' => (float) $data['w_praktikum'],
            'w_tugas' => (float) $data['w_tugas'],
            'w_uts' => (float) $data['w_uts'],
            'w_uas' => (float) $data['w_uas'],
            'sum_weights_used' => $sumWeights,
            'final_score' => $finalScore,
            'predikat' => $predikat,
            'category' => $category,
        ]);
        $sg->save();

        return redirect()->route('admin.grades.student.show', ['student' => $student->id, 'subject_id' => (int) $data['subject_id']])
            ->with('status', 'Nilai & bobot tersimpan. Nilai akhir: ' . ($finalScore ?? '—') . ($category ? " • Keterangan: $category" : ''));
    }

    public function exportStudentPdf(Request $request, User $student)
    {
        $subjects = Subject::orderBy('name')->get();
        $subjectId = (int) $request->query('subject_id', $subjects->first()->id ?? 0);

        $wAbsen = (float) $request->query('w_absen', 10);
        $wPraktikum = (float) $request->query('w_praktikum', 10);
        $wTugas = (float) $request->query('w_tugas', 20);
        $wUts = (float) $request->query('w_uts', 30);
        $wUas = (float) $request->query('w_uas', 30);

        $attendanceScore = null;
        if ($subjectId) {
            $attTotal = Attendance::where('subject_id', $subjectId)
                ->where('student_id', $student->id)
                ->count();
            if ($attTotal > 0) {
                $hadir = Attendance::where('subject_id', $subjectId)
                    ->where('student_id', $student->id)
                    ->where('status', 'hadir')
                    ->count();
                $attendanceScore = round(($hadir / $attTotal) * 100, 2);
            }
        }

        $latestUts = ExamResult::with(['exam'])
            ->whereHas('exam', fn ($q) => $q->where('subject_id', $subjectId)->where('type', 'UTS'))
            ->where('student_id', $student->id)
            ->orderByDesc('submitted_at')
            ->orderByDesc('created_at')
            ->first();
        $latestUas = ExamResult::with(['exam'])
            ->whereHas('exam', fn ($q) => $q->where('subject_id', $subjectId)->where('type', 'UAS'))
            ->where('student_id', $student->id)
            ->orderByDesc('submitted_at')
            ->orderByDesc('created_at')
            ->first();

        $latestSubmissions = AssignmentSubmission::with(['assignment'])
            ->whereHas('assignment', fn ($q) => $q->where('subject_id', $subjectId))
            ->where('student_id', $student->id)
            ->whereNotNull('score')
            ->orderByDesc('submitted_at')
            ->get();

        $tugasScore = null; $praktikumScore = null;
        foreach ($latestSubmissions as $sub) {
            $title = strtolower((string)($sub->assignment?->title ?? ''));
            $desc = strtolower((string)($sub->assignment?->description ?? ''));
            $isPraktikum = str_contains($title, 'praktikum') || str_contains($desc, 'praktikum');
            if ($isPraktikum && is_null($praktikumScore)) {
                $praktikumScore = $sub->score;
            } elseif (is_null($tugasScore)) {
                $tugasScore = $sub->score;
            }
        }

        $utsScore = $latestUts?->score;
        $uasScore = $latestUas?->score;

        $pairs = [
            ['score' => $attendanceScore, 'weight' => $wAbsen],
            ['score' => $praktikumScore, 'weight' => $wPraktikum],
            ['score' => $tugasScore, 'weight' => $wTugas],
            ['score' => $utsScore, 'weight' => $wUts],
            ['score' => $uasScore, 'weight' => $wUas],
        ];
        $sumWeights = 0.0; $sum = 0.0;
        foreach ($pairs as $p) {
            if (!is_null($p['score']) && $p['weight'] > 0) {
                $sumWeights += (float) $p['weight'];
                $sum += (float) $p['score'] * (float) $p['weight'];
            }
        }
        $finalScore = $sumWeights > 0 ? round($sum / $sumWeights, 2) : null;

        $title = 'Nilai Siswa - ' . ($student->name ?? 'Mahasiswa');
        return app('report.pdf')->make($title, 'guru.grades.export_pdf_one', [
            'student' => $student,
            'subject' => Subject::find($subjectId),
            'weights' => compact('wAbsen', 'wPraktikum', 'wTugas', 'wUts', 'wUas'),
            'components' => compact('attendanceScore', 'praktikumScore', 'tugasScore', 'utsScore', 'uasScore'),
            'finalScore' => $finalScore,
        ]);
    }

    public function summary(Request $request): View
    {
        $subjectId = $request->query('subject_id');
        $classroom = $request->query('classroom');
        $q = strtolower(trim((string) $request->query('q')));

        $wUts = (float) $request->query('w_uts', 30);
        $wUas = (float) $request->query('w_uas', 30);
        $wTugas = (float) $request->query('w_tugas', 20);
        $wPraktikum = (float) $request->query('w_praktikum', 20);

        $subjects = Subject::orderBy('name')->get();
        $classrooms = Classroom::orderBy('name')->pluck('name');

        $examResults = ExamResult::with(['student', 'exam.subject'])
            ->when($subjectId, fn ($q2) => $q2->whereHas('exam', fn ($e) => $e->where('subject_id', $subjectId)))
            ->when($classroom, fn ($q3) => $q3->whereHas('student', fn ($s) => $s->where('classroom', $classroom)))
            ->orderByDesc('submitted_at')
            ->get();

        $taskAndPraktikum = AssignmentSubmission::with(['student', 'assignment.subject'])
            ->whereNotNull('score')
            ->when($subjectId, fn ($q4) => $q4->whereHas('assignment', fn ($a) => $a->where('subject_id', $subjectId)))
            ->when($classroom, fn ($q5) => $q5->whereHas('student', fn ($s) => $s->where('classroom', $classroom)))
            ->orderByDesc('submitted_at')
            ->get();

        $summaries = [];
        foreach ($examResults as $res) {
            $subjectName = $res->exam?->subject?->name ?? '—';
            $key = $res->student_id . ':' . ($res->exam?->subject_id ?? 0);
            if (!isset($summaries[$key])) {
                $summaries[$key] = [
                    'student_name' => $res->student?->name ?? '—',
                    'classroom' => $res->student?->classroom ?? '—',
                    'subject' => $subjectName,
                    'uts' => null,
                    'uas' => null,
                    'tugas' => null,
                    'praktikum' => null,
                ];
            }
            $type = $res->exam?->type;
            if ($type === 'UTS') {
                $summaries[$key]['uts'] = ['score' => $res->score];
            } elseif ($type === 'UAS') {
                $summaries[$key]['uas'] = ['score' => $res->score];
            }
        }
        foreach ($taskAndPraktikum as $sub) {
            $subjectIdRow = $sub->assignment?->subject_id ?? 0;
            $key = $sub->student_id . ':' . $subjectIdRow;
            if (!isset($summaries[$key])) {
                $summaries[$key] = [
                    'student_name' => $sub->student?->name ?? '—',
                    'classroom' => $sub->student?->classroom ?? '—',
                    'subject' => $sub->assignment?->subject?->name ?? '—',
                    'uts' => null,
                    'uas' => null,
                    'tugas' => null,
                    'praktikum' => null,
                ];
            }
            $title = strtolower((string)($sub->assignment?->title ?? ''));
            $desc = strtolower((string)($sub->assignment?->description ?? ''));
            $isPraktikum = str_contains($title, 'praktikum') || str_contains($desc, 'praktikum');
            if ($isPraktikum) {
                $summaries[$key]['praktikum'] = ['score' => $sub->score];
            } else {
                $summaries[$key]['tugas'] = ['score' => $sub->score];
            }
        }

        $gradeSummary = collect($summaries)->map(function ($row) use ($wUts, $wUas, $wTugas, $wPraktikum) {
            $uts = is_array($row['uts']) ? $row['uts']['score'] : $row['uts'];
            $uas = is_array($row['uas']) ? $row['uas']['score'] : $row['uas'];
            $tugas = is_array($row['tugas']) ? $row['tugas']['score'] : $row['tugas'];
            $praktikum = is_array($row['praktikum']) ? $row['praktikum']['score'] : $row['praktikum'];

            $pairs = [
                ['score' => $uts, 'weight' => $wUts],
                ['score' => $uas, 'weight' => $wUas],
                ['score' => $tugas, 'weight' => $wTugas],
                ['score' => $praktikum, 'weight' => $wPraktikum],
            ];
            $sumWeights = 0.0; $sum = 0.0;
            foreach ($pairs as $p) {
                if (!is_null($p['score']) && $p['weight'] > 0) {
                    $sumWeights += (float) $p['weight'];
                    $sum += (float) $p['score'] * (float) $p['weight'];
                }
            }
            $avg = $sumWeights > 0 ? round($sum / $sumWeights, 2) : null;

            return (object) [
                'student_name' => $row['student_name'],
                'classroom' => $row['classroom'],
                'subject' => $row['subject'],
                'uts' => is_null($uts) ? '—' : round((float) $uts, 2),
                'uas' => is_null($uas) ? '—' : round((float) $uas, 2),
                'tugas' => is_null($tugas) ? '—' : round((float) $tugas, 2),
                'praktikum' => is_null($praktikum) ? '—' : round((float) $praktikum, 2),
                'avg' => $avg ?? '—',
                'predikat' => is_null($avg) ? '—' : $this->gradeLetter($avg),
            ];
        })->values();

        if (!empty($q)) {
            $gradeSummary = $gradeSummary->filter(function ($row) use ($q) {
                $p = strtolower($row->predikat ?? '');
                return str_contains(strtolower($row->student_name), $q)
                    || str_contains(strtolower($row->subject), $q)
                    || str_contains(strtolower($row->classroom), $q)
                    || (!empty($p) && str_contains($p, $q));
            })->values();
        }

        $weights = [
            'w_uts' => $wUts,
            'w_uas' => $wUas,
            'w_tugas' => $wTugas,
            'w_praktikum' => $wPraktikum,
        ];

        return view('admin.grades.summary', compact('subjects', 'subjectId', 'classrooms', 'classroom', 'q', 'weights', 'gradeSummary'));
    }

    /**
     * Export ringkasan nilai (UTS/UAS/Tugas/Praktikum) ke PDF sesuai filter & pencarian untuk Admin.
     */
    public function exportSummaryPdf(Request $request)
    {
        $subjectId = $request->query('subject_id');
        $classroom = $request->query('classroom');
        $q = strtolower(trim((string) $request->query('q')));
        $wUts = (float) $request->query('w_uts', 30);
        $wUas = (float) $request->query('w_uas', 30);
        $wTugas = (float) $request->query('w_tugas', 20);
        $wPraktikum = (float) $request->query('w_praktikum', 20);

        $examResults = ExamResult::with(['student', 'exam.subject'])
            ->when($subjectId, fn ($q2) => $q2->whereHas('exam', fn ($e) => $e->where('subject_id', $subjectId)))
            ->when($classroom, fn ($q3) => $q3->whereHas('student', fn ($s) => $s->where('classroom', $classroom)))
            ->orderByDesc('submitted_at')
            ->get();

        $taskAndPraktikum = AssignmentSubmission::with(['student', 'assignment.subject'])
            ->whereNotNull('score')
            ->when($subjectId, fn ($q4) => $q4->whereHas('assignment', fn ($a) => $a->where('subject_id', $subjectId)))
            ->when($classroom, fn ($q5) => $q5->whereHas('student', fn ($s) => $s->where('classroom', $classroom)))
            ->orderByDesc('submitted_at')
            ->get();

        $summaries = [];
        foreach ($examResults as $res) {
            $subjectName = $res->exam?->subject?->name ?? '—';
            $key = $res->student_id . ':' . ($res->exam?->subject_id ?? 0);
            if (!isset($summaries[$key])) {
                $summaries[$key] = [
                    'student_name' => $res->student?->name ?? '—',
                    'classroom' => $res->student?->classroom ?? '—',
                    'subject' => $subjectName,
                    'uts' => null,
                    'uas' => null,
                    'tugas' => null,
                    'praktikum' => null,
                ];
            }
            $type = $res->exam?->type;
            if ($type === 'UTS') {
                $summaries[$key]['uts'] = ['score' => $res->score];
            } elseif ($type === 'UAS') {
                $summaries[$key]['uas'] = ['score' => $res->score];
            }
        }
        foreach ($taskAndPraktikum as $sub) {
            $subjectIdRow = $sub->assignment?->subject_id ?? 0;
            $key = $sub->student_id . ':' . $subjectIdRow;
            if (!isset($summaries[$key])) {
                $summaries[$key] = [
                    'student_name' => $sub->student?->name ?? '—',
                    'classroom' => $sub->student?->classroom ?? '—',
                    'subject' => $sub->assignment?->subject?->name ?? '—',
                    'uts' => null,
                    'uas' => null,
                    'tugas' => null,
                    'praktikum' => null,
                ];
            }
            $title = strtolower((string)($sub->assignment?->title ?? ''));
            $desc = strtolower((string)($sub->assignment?->description ?? ''));
            $isPraktikum = str_contains($title, 'praktikum') || str_contains($desc, 'praktikum');
            if ($isPraktikum) {
                $summaries[$key]['praktikum'] = ['score' => $sub->score];
            } else {
                $summaries[$key]['tugas'] = ['score' => $sub->score];
            }
        }

        $rows = collect($summaries)->map(function ($row) use ($wUts, $wUas, $wTugas, $wPraktikum) {
            $uts = is_array($row['uts']) ? $row['uts']['score'] : $row['uts'];
            $uas = is_array($row['uas']) ? $row['uas']['score'] : $row['uas'];
            $tugas = is_array($row['tugas']) ? $row['tugas']['score'] : $row['tugas'];
            $praktikum = is_array($row['praktikum']) ? $row['praktikum']['score'] : $row['praktikum'];
            $pairs = [
                ['score' => $uts, 'weight' => $wUts],
                ['score' => $uas, 'weight' => $wUas],
                ['score' => $tugas, 'weight' => $wTugas],
                ['score' => $praktikum, 'weight' => $wPraktikum],
            ];
            $sumWeights = 0.0; $sum = 0.0;
            foreach ($pairs as $p) {
                if (!is_null($p['score']) && $p['weight'] > 0) {
                    $sumWeights += (float) $p['weight'];
                    $sum += (float) $p['score'] * (float) $p['weight'];
                }
            }
            $avg = $sumWeights > 0 ? round($sum / $sumWeights, 2) : null;
            return [
                'Mahasiswa' => $row['student_name'],
                'Kelas' => $row['classroom'],
                'Mata Kuliah' => $row['subject'],
                'Nilai UTS' => is_null($uts) ? '—' : round((float) $uts, 2),
                'Nilai UAS' => is_null($uas) ? '—' : round((float) $uas, 2),
                'Nilai Tugas' => is_null($tugas) ? '—' : round((float) $tugas, 2),
                'Nilai Praktikum' => is_null($praktikum) ? '—' : round((float) $praktikum, 2),
                'Rata-rata' => $avg ?? '—',
                'Predikat' => is_null($avg) ? '—' : $this->gradeLetter($avg),
            ];
        })->values();

        if (!empty($q)) {
            $rows = $rows->filter(function ($row) use ($q) {
                return str_contains(strtolower($row['Mahasiswa']), $q)
                    || str_contains(strtolower($row['Mata Kuliah']), $q)
                    || str_contains(strtolower($row['Kelas']), $q)
                    || str_contains(strtolower($row['Predikat']), $q);
            })->values();
        }

        $title = 'Ringkasan Nilai (Admin) - ' . now()->format('Ymd-His');
        return app('report.pdf')->make($title, 'guru.grades.export_pdf', [
            'rows' => $rows,
            'filters' => [
                'subject_id' => $subjectId,
                'classroom' => $classroom,
                'q' => $request->query('q'),
                'w_uts' => $wUts,
                'w_uas' => $wUas,
                'w_tugas' => $wTugas,
                'w_praktikum' => $wPraktikum,
            ],
        ]);
    }

    /**
     * Export ringkasan nilai ke Excel (xlsx) sesuai filter & pencarian untuk Admin.
     */
    public function exportSummaryExcel(Request $request)
    {
        $subjectId = $request->query('subject_id');
        $classroom = $request->query('classroom');
        $q = strtolower(trim((string) $request->query('q')));
        $wUts = (float) $request->query('w_uts', 30);
        $wUas = (float) $request->query('w_uas', 30);
        $wTugas = (float) $request->query('w_tugas', 20);
        $wPraktikum = (float) $request->query('w_praktikum', 20);

        $examResults = ExamResult::with(['student', 'exam.subject'])
            ->when($subjectId, fn ($q2) => $q2->whereHas('exam', fn ($e) => $e->where('subject_id', $subjectId)))
            ->when($classroom, fn ($q3) => $q3->whereHas('student', fn ($s) => $s->where('classroom', $classroom)))
            ->orderByDesc('submitted_at')
            ->get();

        $taskAndPraktikum = AssignmentSubmission::with(['student', 'assignment.subject'])
            ->whereNotNull('score')
            ->when($subjectId, fn ($q4) => $q4->whereHas('assignment', fn ($a) => $a->where('subject_id', $subjectId)))
            ->when($classroom, fn ($q5) => $q5->whereHas('student', fn ($s) => $s->where('classroom', $classroom)))
            ->orderByDesc('submitted_at')
            ->get();

        $summaries = [];
        foreach ($examResults as $res) {
            $subjectName = $res->exam?->subject?->name ?? '—';
            $key = $res->student_id . ':' . ($res->exam?->subject_id ?? 0);
            if (!isset($summaries[$key])) {
                $summaries[$key] = [
                    'student_name' => $res->student?->name ?? '—',
                    'classroom' => $res->student?->classroom ?? '—',
                    'subject' => $subjectName,
                    'uts' => null,
                    'uas' => null,
                    'tugas' => null,
                    'praktikum' => null,
                ];
            }
            $type = $res->exam?->type;
            if ($type === 'UTS') {
                $summaries[$key]['uts'] = ['score' => $res->score];
            } elseif ($type === 'UAS') {
                $summaries[$key]['uas'] = ['score' => $res->score];
            }
        }
        foreach ($taskAndPraktikum as $sub) {
            $subjectIdRow = $sub->assignment?->subject_id ?? 0;
            $key = $sub->student_id . ':' . $subjectIdRow;
            if (!isset($summaries[$key])) {
                $summaries[$key] = [
                    'student_name' => $sub->student?->name ?? '—',
                    'classroom' => $sub->student?->classroom ?? '—',
                    'subject' => $sub->assignment?->subject?->name ?? '—',
                    'uts' => null,
                    'uas' => null,
                    'tugas' => null,
                    'praktikum' => null,
                ];
            }
            $title = strtolower((string)($sub->assignment?->title ?? ''));
            $desc = strtolower((string)($sub->assignment?->description ?? ''));
            $isPraktikum = str_contains($title, 'praktikum') || str_contains($desc, 'praktikum');
            if ($isPraktikum) {
                $summaries[$key]['praktikum'] = ['score' => $sub->score];
            } else {
                $summaries[$key]['tugas'] = ['score' => $sub->score];
            }
        }

        $rows = collect($summaries)->map(function ($row) use ($wUts, $wUas, $wTugas, $wPraktikum) {
            $uts = is_array($row['uts']) ? $row['uts']['score'] : $row['uts'];
            $uas = is_array($row['uas']) ? $row['uas']['score'] : $row['uas'];
            $tugas = is_array($row['tugas']) ? $row['tugas']['score'] : $row['tugas'];
            $praktikum = is_array($row['praktikum']) ? $row['praktikum']['score'] : $row['praktikum'];
            $pairs = [
                ['score' => $uts, 'weight' => $wUts],
                ['score' => $uas, 'weight' => $wUas],
                ['score' => $tugas, 'weight' => $wTugas],
                ['score' => $praktikum, 'weight' => $wPraktikum],
            ];
            $sumWeights = 0.0; $sum = 0.0;
            foreach ($pairs as $p) {
                if (!is_null($p['score']) && $p['weight'] > 0) {
                    $sumWeights += (float) $p['weight'];
                    $sum += (float) $p['score'] * (float) $p['weight'];
                }
            }
            $avg = $sumWeights > 0 ? round($sum / $sumWeights, 2) : null;
            return [
                'Mahasiswa' => $row['student_name'],
                'Kelas' => $row['classroom'],
                'Mata Kuliah' => $row['subject'],
                'Nilai UTS' => is_null($uts) ? '—' : round((float) $uts, 2),
                'Nilai UAS' => is_null($uas) ? '—' : round((float) $uas, 2),
                'Nilai Tugas' => is_null($tugas) ? '—' : round((float) $tugas, 2),
                'Nilai Praktikum' => is_null($praktikum) ? '—' : round((float) $praktikum, 2),
                'Rata-rata' => $avg ?? '—',
                'Predikat' => is_null($avg) ? '—' : $this->gradeLetter($avg),
            ];
        })->values();

        if (!empty($q)) {
            $rows = $rows->filter(function ($row) use ($q) {
                return str_contains(strtolower($row['Mahasiswa']), $q)
                    || str_contains(strtolower($row['Mata Kuliah']), $q)
                    || str_contains(strtolower($row['Kelas']), $q)
                    || str_contains(strtolower($row['Predikat']), $q);
            })->values();
        }

        $title = 'Ringkasan Nilai (Admin) - ' . now()->format('Ymd-His');
        $headings = ['Mahasiswa', 'Kelas', 'Mata Kuliah', 'Nilai UTS', 'Nilai UAS', 'Nilai Tugas', 'Nilai Praktikum', 'Rata-rata', 'Predikat'];

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Ringkasan Nilai');
        $sheet->fromArray([$headings], null, 'A1');
        $sheet->fromArray($rows->map(fn ($r) => array_values($r))->all(), null, 'A2');
        foreach (range('A', 'I') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $writer = new Xlsx($spreadsheet);
        $filename = str_replace(' ', '-', $title) . '.xlsx';
        return response()->streamDownload(function () use ($writer) {
            $writer->save('php://output');
        }, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }
}

