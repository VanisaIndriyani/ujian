<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\Subject;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AttendanceController extends Controller
{
    public function index(Request $request): View
    {
        $subjectId = $request->query('subject_id');

        $attendances = Attendance::with(['student', 'subject'])
            ->when($subjectId, fn ($query) => $query->where('subject_id', $subjectId))
            ->orderByDesc('attendance_date')
            ->paginate(15)
            ->withQueryString();

        $subjects = Subject::orderBy('name')->get();

        return view('admin.attendances.index', compact('attendances', 'subjects', 'subjectId'));
    }

    public function create(): View
    {
        $subjects = Subject::with('students')->orderBy('name')->get();
        $students = User::where('role', 'murid')->orderBy('name')->get();

        return view('admin.attendances.create', compact('subjects', 'students'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'subject_id' => 'required|exists:subjects,id',
            'student_id' => 'required|exists:users,id',
            'attendance_date' => 'required|date',
            'status' => 'required|in:hadir,izin,sakit,alpa',
            'notes' => 'nullable|string',
        ]);

        Attendance::create($data + [
            'recorded_by' => $request->user()->id,
        ]);

        return redirect()->route('admin.attendances.index')
            ->with('success', 'Data absensi berhasil disimpan.');
    }

    public function edit(Attendance $attendance): View
    {
        $subjects = Subject::orderBy('name')->get();
        $students = User::where('role', 'murid')->orderBy('name')->get();

        return view('admin.attendances.edit', compact('attendance', 'subjects', 'students'));
    }

    public function update(Request $request, Attendance $attendance): RedirectResponse
    {
        $data = $request->validate([
            'subject_id' => 'required|exists:subjects,id',
            'student_id' => 'required|exists:users,id',
            'attendance_date' => 'required|date',
            'status' => 'required|in:hadir,izin,sakit,alpa',
            'notes' => 'nullable|string',
        ]);

        $attendance->update($data + [
            'recorded_by' => $request->user()->id,
        ]);

        return redirect()->route('admin.attendances.index')
            ->with('success', 'Data absensi berhasil diperbarui.');
    }

    public function destroy(Attendance $attendance): RedirectResponse
    {
        $attendance->delete();

        return redirect()->route('admin.attendances.index')
            ->with('success', 'Data absensi berhasil dihapus.');
    }
}

