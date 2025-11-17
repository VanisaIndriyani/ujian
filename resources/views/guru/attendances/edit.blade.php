@extends('layouts.guru')

@section('title', 'Ubah Absensi')
@section('header', 'Ubah Absensi Mahasiswa')

@section('content')
<div class="bg-white border border-emerald-100 rounded-2xl shadow p-6 space-y-6">
        <div class="grid md:grid-cols-3 gap-4 text-sm">
            <div>
<p class="text-emerald-500 uppercase tracking-wide text-xs">Mahasiswa</p>
<p class="text-emerald-900 font-semibold mt-1">{{ $attendance->student?->name ?? '-' }}</p>
            </div>
            <div>
<p class="text-emerald-500 uppercase tracking-wide text-xs">Mata Kuliah</p>
<p class="text-emerald-900 font-semibold mt-1">{{ $attendance->subject?->name ?? '-' }}</p>
            </div>
            <div>
<p class="text-emerald-500 uppercase tracking-wide text-xs">Tanggal</p>
<p class="text-emerald-900 font-semibold mt-1">{{ $attendance->attendance_date->format('d M Y') }}</p>
            </div>
        </div>

        <form action="{{ route('guru.attendances.update', $attendance) }}" method="POST" class="space-y-6">
            @csrf
            @method('PUT')

            <div class="grid md:grid-cols-2 gap-5">
                <div>
<label class="block text-sm font-medium text-emerald-600">Status Kehadiran</label>
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
<a href="{{ route('guru.attendances.index') }}" class="px-4 py-2 rounded-lg border border-emerald-200 text-emerald-600">Batal</a>
<button type="submit" class="px-4 py-2 rounded-lg bg-emerald-600 text-white font-medium shadow hover:bg-emerald-700">Simpan Perubahan</button>
            </div>
        </form>
    </div>
@endsection

