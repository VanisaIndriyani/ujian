@extends('layouts.guru')

@section('title', 'Absensi')
@section('header', 'Absensi Mata Kuliah')

@section('content')
    <div class="flex flex-col gap-6">
        <div class="flex flex-wrap items-center justify-between gap-4">
            <div>
                <h2 class="text-lg font-semibold text-emerald-900">Rekap Absensi Jurusan Anda</h2>
                <p class="text-sm text-emerald-500">Catat dan perbarui absensi mahasiswa.</p>
            </div>
            <a href="{{ route('guru.attendances.create') }}" title="Catat Absensi" aria-label="Catat Absensi"
               class="inline-flex items-center justify-center p-2 rounded-lg bg-emerald-600 text-white shadow hover:bg-emerald-700">
                <i class="fa-solid fa-plus"></i>
                <span class="sr-only">Catat Absensi</span>
            </a>
        </div>

        <div class="bg-white border border-emerald-100 rounded-2xl shadow p-6 space-y-4">
            <form method="GET" class="flex flex-wrap items-center gap-3 text-sm">
                <label class="text-emerald-500 font-medium">Filter Mata Kuliah</label>
                <select name="subject_id" class="rounded-lg border border-emerald-200 px-3 py-2 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-transparent">
                    <option value="">Semua</option>
                    @foreach ($subjects as $subject)
                        <option value="{{ $subject->id }}" {{ (int) $subjectId === $subject->id ? 'selected' : '' }}>
                            {{ $subject->name }}
                        </option>
                    @endforeach
                </select>
                <button class="px-3 py-2 rounded-lg bg-emerald-100 text-emerald-600 hover:bg-emerald-200">Terapkan</button>
            </form>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-emerald-100 text-sm">
                    <thead class="bg-emerald-50/60 text-emerald-600 uppercase text-xs tracking-wider">
                        <tr>
                            <th class="px-4 py-3 text-left">Tanggal</th>
                            <th class="px-4 py-3 text-left">Mahasiswa</th>
                            <th class="px-4 py-3 text-left">Jurusan</th>
                            <th class="px-4 py-3 text-left">Mata Kuliah</th>
                            <th class="px-4 py-3 text-left">Status</th>
                            <th class="px-4 py-3 text-left">Catatan</th>
                            <th class="px-4 py-3 text-left">Bukti Kehadiran</th>
                            <th class="px-4 py-3 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-emerald-50">
                        @forelse ($attendances as $attendance)
                            <tr class="hover:bg-emerald-50/40">
                                <td class="px-4 py-3 text-emerald-600">{{ $attendance->attendance_date->format('d M Y') }}</td>
                                <td class="px-4 py-3 text-emerald-600">{{ $attendance->student?->name ?? '—' }}</td>
                                <td class="px-4 py-3 text-emerald-600">{{ $attendance->student?->classroom ?? '—' }}</td>
                                <td class="px-4 py-3 text-emerald-600">{{ $attendance->subject?->name ?? '—' }}</td>
                                <td class="px-4 py-3">
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium
                                        @class([
                                            'bg-emerald-100 text-emerald-700' => $attendance->status === 'hadir',
                                            'bg-emerald-100 text-emerald-700' => $attendance->status === 'izin',
                                            'bg-emerald-100 text-emerald-700' => $attendance->status === 'sakit',
                                            'bg-emerald-100 text-emerald-700' => $attendance->status === 'alpa',
                                        ])">
                                        {{ strtoupper($attendance->status) }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-emerald-600">{{ $attendance->notes ?? '—' }}</td>
                                <td class="px-4 py-3">
                                    @if ($attendance->proof_path)
                                        @php
                                            $fileExists = Storage::disk('public')->exists($attendance->proof_path);
                                            $url = $fileExists ? asset('storage/' . $attendance->proof_path) : null;
                                        @endphp
                                        @if ($url)
                                            <a href="{{ $url }}" target="_blank" class="inline-flex items-center gap-2 text-emerald-600 hover:text-emerald-700">
                                                <i class="fa-solid fa-image"></i>
                                                <span class="text-xs">Lihat Foto</span>
                                            </a>
                                        @else
                                            <span class="text-xs text-emerald-400">Foto tidak ditemukan</span>
                                        @endif
                                    @else
                                        <span class="text-xs text-emerald-400">—</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3">
                                    <div class="flex justify-end gap-2">
                                        <a href="{{ route('guru.attendances.edit', $attendance) }}" title="Ubah" aria-label="Ubah" class="inline-flex items-center justify-center p-2 rounded-lg bg-emerald-100 text-emerald-600 hover:bg-emerald-200">
                                            <i class="fa-solid fa-pen-to-square"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="px-4 py-6 text-center text-emerald-400">Belum ada data absensi.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="border-t border-emerald-100 pt-4">
                {{ $attendances->links() }}
            </div>
        </div>
    </div>
@endsection

