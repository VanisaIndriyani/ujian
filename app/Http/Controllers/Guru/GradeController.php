<?php

namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Controller;
use App\Models\SemesterGrade;
use App\Models\AssignmentSubmission;
use App\Models\ExamResult;
use App\Models\Classroom;
use App\Models\Subject;
use App\Models\Attendance;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class GradeController extends Controller
{
    public function index(Request $request): View
    {
        $guru = Auth::user();
        $subjectIds = $guru->subjectsTeaching()->pluck('id');
        $subjectId = $request->query('subject_id');
        $classroom = $request->query('classroom');
        $q = strtolower(trim((string) $request->query('q')));
        // Bobot komponen (default 30/30/20/20)
        $wUts = (float) $request->query('w_uts', 30);
        $wUas = (float) $request->query('w_uas', 30);
        $wTugas = (float) $request->query('w_tugas', 20);
        $wPraktikum = (float) $request->query('w_praktikum', 20);

        $grades = SemesterGrade::with(['student', 'subject'])
            ->whereIn('subject_id', $subjectIds)
            ->when($subjectId, fn ($query) => $query->where('subject_id', $subjectId))
            ->orderByDesc('created_at')
            ->paginate(15)
            ->withQueryString();

        // Ambil nilai tugas (AssignmentSubmission) yang sudah dinilai oleh guru ini
        $taskGrades = AssignmentSubmission::with(['student', 'assignment.subject'])
            ->whereNotNull('score')
            ->whereHas('assignment', function ($q) use ($subjectIds, $subjectId) {
                $q->whereIn('subject_id', $subjectIds);
                if ($subjectId) {
                    $q->where('subject_id', $subjectId);
                }
            })
            ->orderByDesc('submitted_at')
            ->orderByDesc('created_at')
            ->take(50)
            ->get();

        // Ambil nilai ujian (ExamResult) untuk ujian yang dibuat oleh guru ini
        $examGrades = ExamResult::with(['student', 'exam.subject'])
            ->whereNotNull('score')
            ->whereHas('exam', function ($q) use ($guru, $subjectIds, $subjectId) {
                $q->where('creator_id', $guru->id)
                  ->whereIn('subject_id', $subjectIds);
                if ($subjectId) {
                    $q->where('subject_id', $subjectId);
                }
            })
            ->orderByDesc('submitted_at')
            ->orderByDesc('created_at')
            ->take(50)
            ->get();

        $subjects = Subject::whereIn('id', $subjectIds)->orderBy('name')->get();
        $classrooms = Classroom::orderBy('name')->pluck('name');

        // Daftar siswa untuk tombol Show
        $students = User::query()
            ->where('role', 'murid')
            ->when($classroom, fn ($qq) => $qq->where('classroom', $classroom))
            ->when(!empty($q), fn ($qq) => $qq->where(function ($w) use ($q) {
                $w->where('name', 'like', "%$q%")
                  ->orWhere('classroom', 'like', "%$q%");
            }))
            ->orderBy('name')
            ->paginate(25)
            ->withQueryString();

        // Ringkasan UTS/UAS per mahasiswa per mata kuliah
        $examResults = ExamResult::with(['student', 'exam.subject'])
            ->whereHas('exam', function ($q) use ($guru, $subjectIds, $subjectId) {
                $q->where('creator_id', $guru->id)
                  ->whereIn('subject_id', $subjectIds);
                if ($subjectId) {
                    $q->where('subject_id', $subjectId);
                }
            })
            ->when($classroom, fn ($q) => $q->whereHas('student', fn ($s) => $s->where('classroom', $classroom)))
            ->orderByDesc('submitted_at')
            ->get();
        // Ambil submissions tugas/praktikum bernilai (score tidak null)
        $taskAndPraktikum = AssignmentSubmission::with(['student', 'assignment.subject'])
            ->whereNotNull('score')
            ->whereHas('assignment', function ($q) use ($guru, $subjectIds, $subjectId) {
                $q->where('guru_id', $guru->id)
                  ->whereIn('subject_id', $subjectIds);
                if ($subjectId) {
                    $q->where('subject_id', $subjectId);
                }
            })
            ->when($classroom, fn ($q) => $q->whereHas('student', fn ($s) => $s->where('classroom', $classroom)))
            ->orderByDesc('submitted_at')
            ->get();

        // Gabungkan jadi ringkasan per mahasiswa+mata kuliah
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
                $summaries[$key]['uts'] = $this->pickLatest($summaries[$key]['uts'], $res);
            } elseif ($type === 'UAS') {
                $summaries[$key]['uas'] = $this->pickLatest($summaries[$key]['uas'], $res);
            }
        }

        foreach ($taskAndPraktikum as $sub) {
            $subjectIdRow = $sub->assignment?->subject_id ?? 0;
            $subjectName = $sub->assignment?->subject?->name ?? '—';
            $key = $sub->student_id . ':' . $subjectIdRow;
            if (!isset($summaries[$key])) {
                $summaries[$key] = [
                    'student_name' => $sub->student?->name ?? '—',
                    'classroom' => $sub->student?->classroom ?? '—',
                    'subject' => $subjectName,
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
                $summaries[$key]['praktikum'] = $this->pickLatestSubmission($summaries[$key]['praktikum'], $sub);
            } else {
                $summaries[$key]['tugas'] = $this->pickLatestSubmission($summaries[$key]['tugas'], $sub);
            }
        }

        // Hitung rata-rata dan predikat dari komponen yang tersedia
        $gradeSummary = collect($summaries)->map(function ($row) use ($wUts, $wUas, $wTugas, $wPraktikum) {
            $components = [];
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

            $row['uts'] = is_null($uts) ? null : round((float) $uts, 2);
            $row['uas'] = is_null($uas) ? null : round((float) $uas, 2);
            $row['tugas'] = is_null($tugas) ? null : round((float) $tugas, 2);
            $row['praktikum'] = is_null($praktikum) ? null : round((float) $praktikum, 2);
            $row['avg'] = $avg;
            $row['predikat'] = is_null($avg) ? '—' : $this->gradeLetter($avg);
            return (object) $row;
        })->values();

        // Pencarian sederhana pada hasil ringkasan
        if (!empty($q)) {
            $gradeSummary = $gradeSummary->filter(function ($row) use ($q) {
                return str_contains(strtolower($row->student_name), $q)
                    || str_contains(strtolower($row->subject), $q)
                    || str_contains(strtolower($row->classroom), $q)
                    || (!is_null($row->predikat) && str_contains(strtolower($row->predikat), $q));
            })->values();
        }

        $weights = [
            'w_uts' => $wUts,
            'w_uas' => $wUas,
            'w_tugas' => $wTugas,
            'w_praktikum' => $wPraktikum,
        ];

        return view('guru.grades.index', compact('grades', 'subjects', 'subjectId', 'taskGrades', 'examGrades', 'gradeSummary', 'classrooms', 'classroom', 'q', 'weights', 'students'));
    }

    /**
     * Halaman nilai UTS: daftar hasil ujian bertipe UTS dengan filter kelas & mata kuliah,
     * serta form nilai inline untuk dosen.
     */
    public function uts(Request $request): View
    {
        $guru = Auth::user();
        $subjectIds = $guru->subjectsTeaching()->pluck('id');
        $subjectId = $request->query('subject_id');
        $classroom = $request->query('classroom');

        $subjects = Subject::whereIn('id', $subjectIds)->orderBy('name')->get();
        $classrooms = Classroom::orderBy('name')->pluck('name');

        $results = ExamResult::with(['student', 'exam.subject'])
            ->whereHas('exam', function ($q) use ($guru, $subjectIds, $subjectId) {
                $q->where('creator_id', $guru->id)
                  ->whereIn('subject_id', $subjectIds)
                  ->where('type', 'UTS');
                if ($subjectId) {
                    $q->where('subject_id', $subjectId);
                }
            })
            ->when($classroom, fn ($q) => $q->whereHas('student', fn ($s) => $s->where('classroom', $classroom)))
            ->whereNotNull('submitted_at')
            ->orderByDesc('submitted_at')
            ->paginate(25)
            ->withQueryString();

        return view('guru.grades.uts', compact('results', 'subjects', 'classrooms', 'subjectId', 'classroom'));
    }

    /**
     * Halaman nilai UAS: daftar hasil ujian bertipe UAS dengan filter kelas & mata kuliah,
     * serta form nilai inline untuk dosen.
     */
    public function uas(Request $request): View
    {
        $guru = Auth::user();
        $subjectIds = $guru->subjectsTeaching()->pluck('id');
        $subjectId = $request->query('subject_id');
        $classroom = $request->query('classroom');

        $subjects = Subject::whereIn('id', $subjectIds)->orderBy('name')->get();
        $classrooms = Classroom::orderBy('name')->pluck('name');

        $results = ExamResult::with(['student', 'exam.subject'])
            ->whereHas('exam', function ($q) use ($guru, $subjectIds, $subjectId) {
                $q->where('creator_id', $guru->id)
                  ->whereIn('subject_id', $subjectIds)
                  ->where('type', 'UAS');
                if ($subjectId) {
                    $q->where('subject_id', $subjectId);
                }
            })
            ->when($classroom, fn ($q) => $q->whereHas('student', fn ($s) => $s->where('classroom', $classroom)))
            ->whereNotNull('submitted_at')
            ->orderByDesc('submitted_at')
            ->paginate(25)
            ->withQueryString();

        return view('guru.grades.uas', compact('results', 'subjects', 'classrooms', 'subjectId', 'classroom'));
    }

    /**
     * Halaman nilai Tugas: daftar submissions tugas dengan filter kelas & mata kuliah,
     * serta form nilai inline untuk dosen.
     */
    public function tasks(Request $request): View
    {
        $guru = Auth::user();
        $subjectIds = $guru->subjectsTeaching()->pluck('id');
        $subjectId = $request->query('subject_id');
        $classroom = $request->query('classroom');

        $subjects = Subject::whereIn('id', $subjectIds)->orderBy('name')->get();
        $classrooms = Classroom::orderBy('name')->pluck('name');

        $submissions = AssignmentSubmission::with(['student', 'assignment.subject'])
            ->whereHas('assignment', function ($q) use ($guru, $subjectIds, $subjectId) {
                $q->where('guru_id', $guru->id)
                  ->whereIn('subject_id', $subjectIds);
                if ($subjectId) {
                    $q->where('subject_id', $subjectId);
                }
            })
            ->when($classroom, fn ($q) => $q->whereHas('student', fn ($s) => $s->where('classroom', $classroom)))
            ->orderByDesc('submitted_at')
            ->paginate(25)
            ->withQueryString();

        return view('guru.grades.tasks', compact('submissions', 'subjects', 'classrooms', 'subjectId', 'classroom'));
    }

    /**
     * Halaman nilai Praktikum: daftar submissions tugas yang mengandung kata "praktikum"
     * dengan filter kelas & mata kuliah, serta form nilai inline.
     */
    public function praktikum(Request $request): View
    {
        $guru = Auth::user();
        $subjectIds = $guru->subjectsTeaching()->pluck('id');
        $subjectId = $request->query('subject_id');
        $classroom = $request->query('classroom');

        $subjects = Subject::whereIn('id', $subjectIds)->orderBy('name')->get();
        $classrooms = Classroom::orderBy('name')->pluck('name');

        $submissions = AssignmentSubmission::with(['student', 'assignment.subject'])
            ->whereHas('assignment', function ($q) use ($guru, $subjectIds, $subjectId) {
                $q->where('guru_id', $guru->id)
                  ->whereIn('subject_id', $subjectIds)
                  ->where(function ($qq) {
                      $qq->where('title', 'like', '%praktikum%')
                         ->orWhere('description', 'like', '%praktikum%');
                  });
                if ($subjectId) {
                    $q->where('subject_id', $subjectId);
                }
            })
            ->when($classroom, fn ($q) => $q->whereHas('student', fn ($s) => $s->where('classroom', $classroom)))
            ->orderByDesc('submitted_at')
            ->paginate(25)
            ->withQueryString();

        return view('guru.grades.praktikum', compact('submissions', 'subjects', 'classrooms', 'subjectId', 'classroom'));
    }

    private function pickLatest($current, ExamResult $candidate)
    {
        if (is_array($current)) {
            $currentAt = $current['submitted_at'];
            $candAt = $candidate->submitted_at;
            return ($candAt && $currentAt && $candAt->gt($currentAt))
                ? ['score' => $candidate->score, 'submitted_at' => $candidate->submitted_at]
                : $current;
        }
        return ['score' => $candidate->score, 'submitted_at' => $candidate->submitted_at];
    }

    private function pickLatestSubmission($current, AssignmentSubmission $candidate)
    {
        if (is_array($current)) {
            $currentAt = $current['submitted_at'];
            $candAt = $candidate->submitted_at;
            return ($candAt && $currentAt && $candAt->gt($currentAt))
                ? ['score' => $candidate->score, 'submitted_at' => $candidate->submitted_at]
                : $current;
        }
        return ['score' => $candidate->score, 'submitted_at' => $candidate->submitted_at];
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

    public function exportExamGrades(Request $request)
    {
        // Gunakan logika yang sama dengan index
        $this->index($request); // untuk inisialisasi variabel di bawah jika diperlukan

        $guru = Auth::user();
        $subjectIds = $guru->subjectsTeaching()->pluck('id');
        $subjectId = $request->query('subject_id');
        $classroom = $request->query('classroom');

        $examResults = ExamResult::with(['student', 'exam.subject'])
            ->whereHas('exam', function ($q) use ($guru, $subjectIds, $subjectId) {
                $q->where('creator_id', $guru->id)
                  ->whereIn('subject_id', $subjectIds);
                if ($subjectId) {
                    $q->where('subject_id', $subjectId);
                }
            })
            ->when($classroom, fn ($q) => $q->whereHas('student', fn ($s) => $s->where('classroom', $classroom)))
            ->orderByDesc('submitted_at')
            ->get();

        $summaries = [];
        foreach ($examResults as $res) {
            $key = $res->student_id . ':' . ($res->exam?->subject_id ?? 0);
            if (!isset($summaries[$key])) {
                $summaries[$key] = [
                    'student_name' => $res->student?->name ?? '—',
                    'classroom' => $res->student?->classroom ?? '—',
                    'subject' => $res->exam?->subject?->name ?? '—',
                    'uts' => null,
                    'uas' => null,
                ];
            }
            $type = $res->exam?->type;
            if ($type === 'UTS') {
                $summaries[$key]['uts'] = $this->pickLatest($summaries[$key]['uts'], $res);
            } elseif ($type === 'UAS') {
                $summaries[$key]['uas'] = $this->pickLatest($summaries[$key]['uas'], $res);
            }
        }

        $rows = collect($summaries)->map(function ($row) {
            $uts = is_array($row['uts']) ? $row['uts']['score'] : $row['uts'];
            $uas = is_array($row['uas']) ? $row['uas']['score'] : $row['uas'];
            $avg = null;
            if (!is_null($uts) && !is_null($uas)) {
                $avg = round(((float) $uts + (float) $uas) / 2, 2);
            } elseif (!is_null($uts)) {
                $avg = round((float) $uts, 2);
            } elseif (!is_null($uas)) {
                $avg = round((float) $uas, 2);
            }
            $predikat = is_null($avg) ? '—' : $this->gradeLetter($avg);
            return [
                'Mahasiswa' => $row['student_name'],
                'Kelas' => $row['classroom'],
                'Mata Kuliah' => $row['subject'],
                'Nilai UTS' => $uts ?? '—',
                'Nilai UAS' => $uas ?? '—',
                'Rata-rata' => $avg ?? '—',
                'Predikat' => $predikat,
            ];
        })->values();

        $filename = 'nilai-ujian-' . now()->format('Ymd-His') . '.csv';
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function () use ($rows) {
            $output = fopen('php://output', 'w');
            if ($rows->isNotEmpty()) {
                fputcsv($output, array_keys($rows->first()));
                foreach ($rows as $row) {
                    fputcsv($output, array_values($row));
                }
            } else {
                fputcsv($output, ['Mahasiswa', 'Kelas', 'Mata Kuliah', 'Nilai UTS', 'Nilai UAS', 'Rata-rata', 'Predikat']);
            }
            fclose($output);
        };

        return response()->streamDownload($callback, $filename, $headers);
    }

    /** Halaman ringkasan keseluruhan nilai per mahasiswa per mata kuliah (lihat semua). */
    public function summary(Request $request): View
    {
        $guru = Auth::user();
        $subjectIds = $guru->subjectsTeaching()->pluck('id');
        $subjectId = $request->query('subject_id');
        $classroom = $request->query('classroom');
        $q = strtolower(trim((string) $request->query('q')));

        $wUts = (float) $request->query('w_uts', 30);
        $wUas = (float) $request->query('w_uas', 30);
        $wTugas = (float) $request->query('w_tugas', 20);
        $wPraktikum = (float) $request->query('w_praktikum', 20);

        $subjects = Subject::whereIn('id', $subjectIds)->orderBy('name')->get();
        $classrooms = Classroom::orderBy('name')->pluck('name');

        $examResults = ExamResult::with(['student', 'exam.subject'])
            ->whereHas('exam', function ($q2) use ($guru, $subjectIds, $subjectId) {
                $q2->where('creator_id', $guru->id)
                   ->whereIn('subject_id', $subjectIds);
                if ($subjectId) {
                    $q2->where('subject_id', $subjectId);
                }
            })
            ->when($classroom, fn ($q3) => $q3->whereHas('student', fn ($s) => $s->where('classroom', $classroom)))
            ->orderByDesc('submitted_at')
            ->get();

        $taskAndPraktikum = AssignmentSubmission::with(['student', 'assignment.subject'])
            ->whereNotNull('score')
            ->whereHas('assignment', function ($q4) use ($guru, $subjectIds, $subjectId) {
                $q4->where('guru_id', $guru->id)
                   ->whereIn('subject_id', $subjectIds);
                if ($subjectId) {
                    $q4->where('subject_id', $subjectId);
                }
            })
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
                $summaries[$key]['uts'] = $this->pickLatest($summaries[$key]['uts'], $res);
            } elseif ($type === 'UAS') {
                $summaries[$key]['uas'] = $this->pickLatest($summaries[$key]['uas'], $res);
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
                $summaries[$key]['praktikum'] = $this->pickLatestSubmission($summaries[$key]['praktikum'], $sub);
            } else {
                $summaries[$key]['tugas'] = $this->pickLatestSubmission($summaries[$key]['tugas'], $sub);
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

        return view('guru.grades.summary', compact('subjects', 'subjectId', 'classrooms', 'classroom', 'q', 'weights', 'gradeSummary'));
    }

    /**
     * Export ringkasan nilai (UTS/UAS/Tugas/Praktikum) ke PDF sesuai filter & pencarian.
     */
    public function exportSummaryPdf(Request $request)
    {
        $guru = Auth::user();
        $subjectIds = $guru->subjectsTeaching()->pluck('id');
        $subjectId = $request->query('subject_id');
        $classroom = $request->query('classroom');
        $q = strtolower(trim((string) $request->query('q')));
        $wUts = (float) $request->query('w_uts', 30);
        $wUas = (float) $request->query('w_uas', 30);
        $wTugas = (float) $request->query('w_tugas', 20);
        $wPraktikum = (float) $request->query('w_praktikum', 20);

        // Data UTS/UAS
        $examResults = ExamResult::with(['student', 'exam.subject'])
            ->whereHas('exam', function ($q2) use ($guru, $subjectIds, $subjectId) {
                $q2->where('creator_id', $guru->id)
                   ->whereIn('subject_id', $subjectIds);
                if ($subjectId) {
                    $q2->where('subject_id', $subjectId);
                }
            })
            ->when($classroom, fn ($q2) => $q2->whereHas('student', fn ($s) => $s->where('classroom', $classroom)))
            ->orderByDesc('submitted_at')
            ->get();

        // Data tugas/praktikum bernilai
        $taskAndPraktikum = AssignmentSubmission::with(['student', 'assignment.subject'])
            ->whereNotNull('score')
            ->whereHas('assignment', function ($q2) use ($guru, $subjectIds, $subjectId) {
                $q2->where('guru_id', $guru->id)
                   ->whereIn('subject_id', $subjectIds);
                if ($subjectId) {
                    $q2->where('subject_id', $subjectId);
                }
            })
            ->when($classroom, fn ($q2) => $q2->whereHas('student', fn ($s) => $s->where('classroom', $classroom)))
            ->orderByDesc('submitted_at')
            ->get();

        // Bangun ringkasan
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
                $summaries[$key]['uts'] = $this->pickLatest($summaries[$key]['uts'], $res);
            } elseif ($type === 'UAS') {
                $summaries[$key]['uas'] = $this->pickLatest($summaries[$key]['uas'], $res);
            }
        }
        foreach ($taskAndPraktikum as $sub) {
            $subjectIdRow = $sub->assignment?->subject_id ?? 0;
            $subjectName = $sub->assignment?->subject?->name ?? '—';
            $key = $sub->student_id . ':' . $subjectIdRow;
            if (!isset($summaries[$key])) {
                $summaries[$key] = [
                    'student_name' => $sub->student?->name ?? '—',
                    'classroom' => $sub->student?->classroom ?? '—',
                    'subject' => $subjectName,
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
                $summaries[$key]['praktikum'] = $this->pickLatestSubmission($summaries[$key]['praktikum'], $sub);
            } else {
                $summaries[$key]['tugas'] = $this->pickLatestSubmission($summaries[$key]['tugas'], $sub);
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
                'Nilai UTS' => $uts ?? '—',
                'Nilai UAS' => $uas ?? '—',
                'Nilai Tugas' => $tugas ?? '—',
                'Nilai Praktikum' => $praktikum ?? '—',
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

        $title = 'Ringkasan Nilai - ' . now()->format('Ymd-His');
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
     * Export ringkasan nilai ke Excel (xlsx) sesuai filter & pencarian.
     */
    public function exportSummaryExcel(Request $request)
    {
        $guru = Auth::user();
        $subjectIds = $guru->subjectsTeaching()->pluck('id');
        $subjectId = $request->query('subject_id');
        $classroom = $request->query('classroom');
        $q = strtolower(trim((string) $request->query('q')));
        $wUts = (float) $request->query('w_uts', 30);
        $wUas = (float) $request->query('w_uas', 30);
        $wTugas = (float) $request->query('w_tugas', 20);
        $wPraktikum = (float) $request->query('w_praktikum', 20);

        $examResults = ExamResult::with(['student', 'exam.subject'])
            ->whereHas('exam', function ($q2) use ($guru, $subjectIds, $subjectId) {
                $q2->where('creator_id', $guru->id)
                   ->whereIn('subject_id', $subjectIds);
                if ($subjectId) {
                    $q2->where('subject_id', $subjectId);
                }
            })
            ->when($classroom, fn ($q2) => $q2->whereHas('student', fn ($s) => $s->where('classroom', $classroom)))
            ->orderByDesc('submitted_at')
            ->get();

        $taskAndPraktikum = AssignmentSubmission::with(['student', 'assignment.subject'])
            ->whereNotNull('score')
            ->whereHas('assignment', function ($q2) use ($guru, $subjectIds, $subjectId) {
                $q2->where('guru_id', $guru->id)
                   ->whereIn('subject_id', $subjectIds);
                if ($subjectId) {
                    $q2->where('subject_id', $subjectId);
                }
            })
            ->when($classroom, fn ($q2) => $q2->whereHas('student', fn ($s) => $s->where('classroom', $classroom)))
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
                $summaries[$key]['uts'] = $this->pickLatest($summaries[$key]['uts'], $res);
            } elseif ($type === 'UAS') {
                $summaries[$key]['uas'] = $this->pickLatest($summaries[$key]['uas'], $res);
            }
        }
        foreach ($taskAndPraktikum as $sub) {
            $subjectIdRow = $sub->assignment?->subject_id ?? 0;
            $subjectName = $sub->assignment?->subject?->name ?? '—';
            $key = $sub->student_id . ':' . $subjectIdRow;
            if (!isset($summaries[$key])) {
                $summaries[$key] = [
                    'student_name' => $sub->student?->name ?? '—',
                    'classroom' => $sub->student?->classroom ?? '—',
                    'subject' => $subjectName,
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
                $summaries[$key]['praktikum'] = $this->pickLatestSubmission($summaries[$key]['praktikum'], $sub);
            } else {
                $summaries[$key]['tugas'] = $this->pickLatestSubmission($summaries[$key]['tugas'], $sub);
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
                'Nilai UTS' => $uts ?? '—',
                'Nilai UAS' => $uas ?? '—',
                'Nilai Tugas' => $tugas ?? '—',
                'Nilai Praktikum' => $praktikum ?? '—',
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

        $title = 'Ringkasan Nilai - ' . now()->format('Ymd-His');
        $headings = ['Mahasiswa', 'Kelas', 'Mata Kuliah', 'Nilai UTS', 'Nilai UAS', 'Nilai Tugas', 'Nilai Praktikum', 'Rata-rata', 'Predikat'];

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Ringkasan Nilai');
        // Headings
        $sheet->fromArray([$headings], null, 'A1');
        // Rows
        $sheet->fromArray($rows->map(fn ($r) => array_values($r))->all(), null, 'A2');

        // Autosize columns A..I
        foreach (range('A', 'I') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $writer = new Xlsx($spreadsheet);
        $filename = str_replace(' ', '-', $title) . '.xlsx';

        // Stream as response
        return response()->streamDownload(function () use ($writer) {
            $writer->save('php://output');
        }, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }

    /**
     * Tampilkan form Show untuk input nilai & bobot per siswa.
     * Default bobot: 10/10/20/30/30 (absen/praktikum/tugas/UTS/UAS).
     */
    public function showStudent(Request $request, User $student): View
    {
        $guru = Auth::user();
        $subjectIds = $guru->subjectsTeaching()->pluck('id');
        $subjects = Subject::whereIn('id', $subjectIds)->orderBy('name')->get();
        $subjectId = (int) $request->query('subject_id', $subjects->first()->id ?? 0);

        // Default bobot sesuai permintaan
        $wAbsen = (float) $request->query('w_absen', 10);
        $wPraktikum = (float) $request->query('w_praktikum', 10);
        $wTugas = (float) $request->query('w_tugas', 20);
        $wUts = (float) $request->query('w_uts', 30);
        $wUas = (float) $request->query('w_uas', 30);

        // Prefill nilai dari data yang ada
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

        // UTS/UAS terbaru
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

        // Tugas/Praktikum terbaru
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

        // Ambil konfigurasi yang pernah disimpan (jika ada)
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

        // Gunakan nilai tersimpan bila tersedia
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

        // Hitung nilai akhir berbobot dari komponen yang terisi
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

        return view('guru.grades.show', [
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

    /** Simpan nilai & bobot per siswa per mata kuliah ke SemesterGrade (notes JSON). */
    public function saveStudent(Request $request, User $student)
    {
        $guru = Auth::user();
        $subjectIds = $guru->subjectsTeaching()->pluck('id');
        $data = $request->validate([
            'subject_id' => ['required', 'integer', 'in:' . $subjectIds->implode(',')],
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

        // Simpan/Update SemesterGrade
        $sg = SemesterGrade::firstOrNew([
            'student_id' => $student->id,
            'subject_id' => (int) $data['subject_id'],
            'semester' => 'Genap',
        ]);
        $sg->score = $finalScore;
        $sg->recorded_by = $guru->id;
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

        return redirect()->route('guru.grades.student.show', ['student' => $student->id, 'subject_id' => (int) $data['subject_id']])
            ->with('status', 'Nilai & bobot tersimpan. Nilai akhir: ' . ($finalScore ?? '—') . ($category ? " • Keterangan: $category" : ''));
    }

    /** Export PDF nilai berbobot untuk satu siswa pada satu mata kuliah. */
    public function exportStudentPdf(Request $request, User $student)
    {
        $guru = Auth::user();
        $subjectIds = $guru->subjectsTeaching()->pluck('id');
        $subjects = Subject::whereIn('id', $subjectIds)->orderBy('name')->get();
        $subjectId = (int) $request->query('subject_id', $subjects->first()->id ?? 0);

        $wAbsen = (float) $request->query('w_absen', 10);
        $wPraktikum = (float) $request->query('w_praktikum', 10);
        $wTugas = (float) $request->query('w_tugas', 20);
        $wUts = (float) $request->query('w_uts', 30);
        $wUas = (float) $request->query('w_uas', 30);

        // Prefill sama seperti showStudent
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
        $predikat = is_null($finalScore) ? '—' : $this->gradeLetter($finalScore);
        $keterangan = is_null($finalScore) ? '—' : $this->categoryDescription($finalScore);

        $subjectName = optional($subjects->firstWhere('id', $subjectId))->name ?? '—';
        $title = 'Data Nilai Siswa - ' . $student->name . ' - ' . $subjectName . ' - ' . now()->format('Ymd-His');

        return app('report.pdf')->make($title, 'guru.grades.export_student_pdf', [
            'title' => $title,
            'student' => $student,
            'subject' => $subjectName,
            'components' => [
                'attendance' => $attendanceScore,
                'praktikum' => $praktikumScore,
                'tugas' => $tugasScore,
                'uts' => $utsScore,
                'uas' => $uasScore,
            ],
            'weights' => compact('wAbsen', 'wPraktikum', 'wTugas', 'wUts', 'wUas'),
            'finalScore' => $finalScore,
            'predikat' => $predikat,
            'keterangan' => $keterangan,
        ]);
    }
}

