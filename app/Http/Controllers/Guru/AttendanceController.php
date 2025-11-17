<?php

namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\Classroom;
use App\Models\Subject;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AttendanceController extends Controller
{
    public function index(Request $request): View
    {
        $guru = Auth::user();
        $subjectId = $request->query('subject_id');

        $subjectIds = $guru->subjectsTeaching()->pluck('id');

        $attendances = Attendance::with(['student', 'subject'])
            ->whereIn('subject_id', $subjectIds)
            ->when($subjectId, fn ($query) => $query->where('subject_id', $subjectId))
            ->orderByDesc('attendance_date')
            ->paginate(15)
            ->withQueryString();

        $subjects = Subject::whereIn('id', $subjectIds)->orderBy('name')->get();

        return view('guru.attendances.index', compact('attendances', 'subjects', 'subjectId'));
    }

    public function create(): View
    {
        $guru = Auth::user();
        $subjects = $guru->subjectsTeaching()->orderBy('name')->get();

        $subjectId = request()->query('subject_id');
        $classroom = request()->query('classroom');

        // Opsi kelas yang tersedia (berdasarkan tabel classrooms)
        $classrooms = Classroom::orderBy('name')->pluck('name');

        // Daftar murid yang difilter berdasarkan subject & kelas (jika dipilih)
        $students = collect();
        if ($subjectId && $subjects->pluck('id')->contains((int) $subjectId)) {
            $studentsQuery = Subject::find($subjectId)?->students();
            if ($studentsQuery) {
                if ($classroom) {
                    $studentsQuery->where('classroom', $classroom);
                }
                $students = $studentsQuery->orderBy('name')->get();

                // Fallback: jika belum ada mahasiswa terdaftar pada mata kuliah,
                // tampilkan mahasiswa berdasarkan kelas yang dipilih.
                if ($students->isEmpty() && $classroom) {
                    $students = User::query()
                        ->where('role', 'murid')
                        ->where('classroom', $classroom)
                        ->orderBy('name')
                        ->get();
                }
            }
        }

        return view('guru.attendances.create', compact('subjects', 'students', 'subjectId', 'classroom', 'classrooms'));
    }

    public function store(Request $request): RedirectResponse
    {
        $guru = Auth::user();

        $validated = $request->validate([
            'subject_id' => 'required|exists:subjects,id',
            'attendance_date' => 'required|date',
            'student_ids' => 'required|array|min:1',
            'student_ids.*' => 'exists:users,id',
            'status' => 'nullable|array',
            'status.*' => 'in:hadir,izin,sakit,alpa',
            'status_default' => 'nullable|in:hadir,izin,sakit,alpa',
            'notes' => 'nullable|string',
        ]);

        abort_if(!$guru->subjectsTeaching()->where('subjects.id', $validated['subject_id'])->exists(), 403);

        $subjectId = (int) $validated['subject_id'];
        $date = $validated['attendance_date'];
        $notes = $validated['notes'] ?? null;
        $statusMap = $validated['status'] ?? [];
        $statusDefault = $validated['status_default'] ?? 'hadir';

        foreach ($validated['student_ids'] as $studentId) {
            $status = $statusMap[$studentId] ?? $statusDefault;
            Attendance::updateOrCreate(
                [
                    'subject_id' => $subjectId,
                    'student_id' => $studentId,
                    'attendance_date' => $date,
                ],
                [
                    'subject_id' => $subjectId,
                    'student_id' => $studentId,
                    'attendance_date' => $date,
                    'status' => $status,
                    'notes' => $notes,
                    'recorded_by' => $guru->id,
                ]
            );
        }

        return redirect()->route('guru.attendances.index')
            ->with('success', 'Absensi berhasil disimpan untuk ' . count($validated['student_ids']) . ' mahasiswa.');
    }

    public function edit(Attendance $attendance): View
    {
        $this->authorizeAttendance($attendance);

        $subjects = Auth::user()->subjectsTeaching()->orderBy('name')->get();

        return view('guru.attendances.edit', compact('attendance', 'subjects'));
    }

    public function update(Request $request, Attendance $attendance): RedirectResponse
    {
        $this->authorizeAttendance($attendance);

        $data = $request->validate([
            'status' => 'required|in:hadir,izin,sakit,alpa',
            'notes' => 'nullable|string',
        ]);

        $attendance->update($data);

        return redirect()->route('guru.attendances.index')
            ->with('success', 'Absensi berhasil diperbarui.');
    }

    protected function authorizeAttendance(Attendance $attendance): void
    {
        $guru = Auth::user();
        abort_if(!$guru->subjectsTeaching()->where('subjects.id', $attendance->subject_id)->exists(), 403);
    }
}

