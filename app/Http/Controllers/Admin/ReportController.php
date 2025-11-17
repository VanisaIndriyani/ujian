<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\Subject;
use App\Models\Classroom;
use App\Models\SemesterGrade;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportController extends Controller
{
    public function attendance(Request $request)
    {
        $format = $request->query('format');

        $startDate = $request->query('start_date');
        $endDate = $request->query('end_date');
        $subjectId = $request->query('subject_id');
        $classroom = $request->query('classroom');
        $status = $request->query('status');
        $q = trim((string) $request->query('q'));

        $baseQuery = Attendance::with(['student', 'subject', 'recorder'])
            ->when($subjectId, fn ($q2) => $q2->where('subject_id', $subjectId))
            ->when($classroom, fn ($q3) => $q3->whereHas('student', fn ($s) => $s->where('classroom', $classroom)))
            ->when(!empty($status), fn ($q4) => $q4->where('status', $status))
            ->when(!empty($q), fn ($q5) => $q5->whereHas('student', fn ($s) => $s->where('name', 'like', "%$q%")));

        if ($startDate && $endDate) {
            $baseQuery->whereBetween('attendance_date', [$startDate, $endDate]);
        } elseif ($startDate) {
            $baseQuery->whereDate('attendance_date', '>=', $startDate);
        } elseif ($endDate) {
            $baseQuery->whereDate('attendance_date', '<=', $endDate);
        }

        if ($format === 'excel') {
            $rows = (clone $baseQuery)
                ->orderBy('attendance_date')
                ->get()
                ->map(function ($item) {
                    return [
                        $item->attendance_date->format('Y-m-d'),
                        $item->student?->name,
                        $item->subject?->name,
                        ucfirst($item->status),
                        $item->recorder?->name ?? '-',
                    ];
                });

            return $this->exportCsv(
                'laporan_absensi.csv',
                ['Tanggal', 'Siswa', 'Mata Pelajaran', 'Status', 'Pencatat'],
                $rows
            );
        }

        if ($format === 'pdf') {
            $records = (clone $baseQuery)
                ->orderBy('attendance_date')
                ->get();

            return app('report.pdf')->make(
                'Laporan Absensi',
                'admin.reports.partials.attendance-table',
                ['records' => $records]
            );
        }
        $records = (clone $baseQuery)
            ->orderByDesc('attendance_date')
            ->paginate(25)
            ->withQueryString();

        $statusCounts = (clone $baseQuery)
            ->selectRaw('status, count(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        $total = ($statusCounts['hadir'] ?? 0)
            + ($statusCounts['izin'] ?? 0)
            + ($statusCounts['sakit'] ?? 0)
            + ($statusCounts['alpa'] ?? 0);
        $presentPct = $total > 0 ? round((($statusCounts['hadir'] ?? 0) / $total) * 100) : 0;

        $chartData = (clone $baseQuery)
            ->selectRaw('attendance_date, count(*) as total')
            ->groupBy('attendance_date')
            ->orderBy('attendance_date')
            ->get()
            ->map(fn ($item) => [
                'date' => $item->attendance_date->format('d M'),
                'total' => $item->total,
            ]);

        $subjects = Subject::orderBy('name')->get();
        $classrooms = Classroom::orderBy('name')->pluck('name');

        return view('admin.reports.attendance', compact(
            'records',
            'subjects',
            'classrooms',
            'subjectId',
            'classroom',
            'status',
            'q',
            'startDate',
            'endDate',
            'statusCounts',
            'total',
            'presentPct',
            'chartData'
        ));
    }

    public function grades(Request $request)
    {
        $records = SemesterGrade::with(['student', 'subject'])
            ->orderByDesc('created_at')
            ->get();

        $format = $request->query('format');

        if ($format === 'excel') {
            return $this->exportCsv(
                'laporan_nilai.csv',
                ['Siswa', 'Mata Pelajaran', 'Semester', 'Nilai', 'Catatan'],
                $records->map(function ($item) {
                    return [
                        $item->student?->name,
                        $item->subject?->name,
                        $item->semester,
                        $item->score,
                        $item->notes,
                    ];
                })
            );
        }

        if ($format === 'pdf') {
            return app('report.pdf')->make(
                'Laporan Nilai',
                'admin.reports.partials.grades-table',
                ['records' => $records]
            );
        }

        return view('admin.reports.grades', compact('records'));
    }

    private function exportCsv(string $filename, array $headers, $rows): StreamedResponse
    {
        return response()->streamDownload(function () use ($headers, $rows) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, $headers);

            foreach ($rows as $row) {
                fputcsv($handle, $row);
            }

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv',
        ]);
    }
}

