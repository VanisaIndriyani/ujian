@extends('layouts.admin')

@section('title', 'Ubah Absensi')
@section('header', 'Ubah Data Absensi')

@section('content')
    <div class="bg-white border border-emerald-100 rounded-2xl shadow p-6">
        <form action="{{ route('admin.attendances.update', $attendance) }}" method="POST" class="space-y-6">
            @csrf
            @method('PUT')

            <div class="grid md:grid-cols-2 gap-5">
                <div>
<label class="block text-sm font-medium text-emerald-600">Mata Kuliah</label>
                    <select name="subject_id" class="mt-1 w-full rounded-xl border border-emerald-200 px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-transparent" required>
                        @foreach ($subjects as $subject)
                            <option value="{{ $subject->id }}" {{ (int) old('subject_id', $attendance->subject_id) === $subject->id ? 'selected' : '' }}>
                                {{ $subject->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-emerald-600">Mahasiswa</label>
                    <select name="student_id" class="mt-1 w-full rounded-xl border border-emerald-200 px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-transparent" required>
                        @foreach ($students as $student)
                            <option value="{{ $student->id }}" {{ (int) old('student_id', $attendance->student_id) === $student->id ? 'selected' : '' }}>
                                {{ $student->name }} ({{ $student->nisn ?? 'NIM -' }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-emerald-600">Tanggal</label>
                    <input type="date" name="attendance_date" value="{{ old('attendance_date', $attendance->attendance_date->toDateString()) }}" class="mt-1 w-full rounded-xl border border-emerald-200 px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-transparent" required>
                </div>

                <div>
                    <label class="block text-sm font-medium text-emerald-600">Status</label>
                    <select name="status" class="mt-1 w-full rounded-xl border border-emerald-200 px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-transparent" required>
                        @foreach (['hadir' => 'Hadir', 'izin' => 'Izin', 'sakit' => 'Sakit', 'alpa' => 'Alpa'] as $value => $label)
                            <option value="{{ $value }}" {{ old('status', $attendance->status) === $value ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-emerald-600">Catatan</label>
                    <textarea name="notes" rows="3" class="mt-1 w-full rounded-xl border border-emerald-200 px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-transparent" placeholder="Opsional...">{{ old('notes', $attendance->notes) }}</textarea>
                </div>
            </div>

            <div class="flex items-center justify-end gap-2">
                <a href="{{ route('admin.attendances.index') }}" class="px-4 py-2 rounded-lg border border-emerald-200 text-emerald-600">Batal</a>
                <button type="submit" class="px-4 py-2 rounded-lg bg-emerald-600 text-white font-medium shadow hover:bg-emerald-700">Simpan Perubahan</button>
            </div>
        </form>
    </div>
@endsection

