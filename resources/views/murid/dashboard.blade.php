@extends('layouts.murid')

@section('title', 'Dashboard Mahasiswa')
@section('header', 'Halo, ' . $murid->name)

@section('content')
    <div class="bg-white rounded-2xl shadow border border-emerald-100 p-6 mb-6 flex items-center gap-6">
        <img src="{{ $murid->profile_photo_url }}" alt="Foto Mahasiswa" class="h-32 w-32 rounded-2xl object-cover ring-4 ring-emerald-100">
        <div>
            <p class="text-sm text-emerald-500 uppercase tracking-wide">Selamat Datang</p>
            <h2 class="text-2xl md:text-3xl font-semibold text-emerald-900">{{ $murid->name }}</h2>
            <p class="text-sm text-emerald-400">Semoga studi kamu lancar dan menyenangkan 🎓</p>
        </div>
    </div>
    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-6 mb-6">
<div class="bg-white rounded-2xl shadow p-6 border border-emerald-100">
<p class="text-sm text-emerald-500 font-medium">Tugas Aktif</p>
<p class="text-3xl font-semibold text-emerald-900 mt-2">{{ $activeAssignments->count() }}</p>
<p class="text-xs text-emerald-400 mt-1">Tugas yang perlu dikerjakan</p>
        </div>
<div class="bg-white rounded-2xl shadow p-6 border border-emerald-100">
<p class="text-sm text-emerald-500 font-medium">Ujian Matakuliah Mendatang</p>
<p class="text-3xl font-semibold text-emerald-900 mt-2">{{ $upcomingExams->count() }}</p>
<p class="text-xs text-emerald-400 mt-1">Siapkan diri untuk ujian matakuliah</p>
        </div>
<div class="bg-white rounded-2xl shadow p-6 border border-emerald-100">
<p class="text-sm text-emerald-500 font-medium">Nilai Ujian Matakuliah Terakhir</p>
<p class="text-3xl font-semibold text-emerald-900 mt-2">{{ optional($latestScores->first())->score ?? '-' }}</p>
<p class="text-xs text-emerald-400 mt-1">Skor ujian matakuliah terbaru</p>
        </div>
<div class="bg-white rounded-2xl shadow p-6 border border-emerald-100">
<p class="text-sm text-emerald-500 font-medium">Presentase Kehadiran</p>
            @php
                $totalAttendance = $attendanceSummary->sum();
                $present = $attendanceSummary->get('hadir', 0);
                $percentage = $totalAttendance > 0 ? round(($present / $totalAttendance) * 100) : 0;
            @endphp
<p class="text-3xl font-semibold text-emerald-900 mt-2">{{ $percentage }}%</p>
<p class="text-xs text-emerald-400 mt-1">Kehadiran sepanjang semester</p>
        </div>
    </div>

    <div class="grid grid-cols-1 xl:grid-cols-3 gap-6">
        <div class="xl:col-span-2 space-y-6">
<div class="bg-white rounded-2xl shadow border border-emerald-100 p-6">
<h2 class="text-lg font-semibold text-emerald-900 flex items-center gap-2">
                    <span>📝</span> Tugas Aktif
                </h2>
                <ul class="mt-4 space-y-3">
                    @forelse ($activeAssignments as $assignment)
<li class="border border-emerald-100 rounded-xl px-4 py-3">
                            <div class="flex justify-between items-center">
                                <div>
<p class="text-sm font-semibold text-emerald-900">{{ $assignment->title }}</p>
<p class="text-xs text-emerald-400">{{ $assignment->subject?->name ?? '-' }}</p>
                                </div>
<span class="text-xs text-emerald-500">
                                    {{ optional($assignment->due_at)->diffForHumans() ?? 'Tanpa batas waktu' }}
                                </span>
                            </div>
                        </li>
                    @empty
<li class="text-sm text-emerald-400">Tidak ada tugas aktif.</li>
                    @endforelse
                </ul>
            </div>

<div class="bg-white rounded-2xl shadow border border-emerald-100 p-6">
<h2 class="text-lg font-semibold text-emerald-900 flex items-center gap-2">
            <span>🧪</span> Ujian Matakuliah Mendatang
                </h2>
                <ul class="mt-4 space-y-3">
                    @forelse ($upcomingExams as $exam)
<li class="border border-emerald-100 rounded-xl px-4 py-3">
                            <div class="flex justify-between items-center">
                                <div>
<p class="text-sm font-semibold text-emerald-900">{{ $exam->title }}</p>
<p class="text-xs text-emerald-400">{{ $exam->subject?->name ?? '-' }}</p>
                                </div>
<span class="text-xs text-emerald-500">{{ optional($exam->start_at)->format('d M Y H:i') }}</span>
                            </div>
                        </li>
                    @empty
<li class="text-sm text-emerald-400">Belum ada jadwal ujian matakuliah.</li>
                    @endforelse
                </ul>
            </div>
        </div>

        <div class="space-y-6">
<div class="bg-white rounded-2xl shadow border border-emerald-100 p-6">
<h2 class="text-lg font-semibold text-emerald-900 mb-4 flex items-center gap-2">
                    <span>📂</span> Riwayat Pengumpulan
                </h2>
                <ul class="space-y-3">
                    @forelse ($submittedAssignments as $submission)
<li class="border border-emerald-100 rounded-xl px-3 py-2">
<p class="text-sm font-medium text-emerald-800">{{ $submission->assignment?->title }}</p>
<p class="text-xs text-emerald-400">
                                Dikirim: {{ optional($submission->submitted_at)->format('d M Y H:i') ?? '-' }}
                            </p>
                        </li>
                    @empty
<li class="text-sm text-emerald-400">Belum ada pengumpulan tugas.</li>
                    @endforelse
                </ul>
            </div>

<div class="bg-white rounded-2xl shadow border border-emerald-100 p-6">
<h2 class="text-lg font-semibold text-emerald-900 mb-4 flex items-center gap-2">
            <span>🏆</span> Nilai Ujian Matakuliah Terakhir
                </h2>
                <ul class="space-y-3">
                    @forelse ($latestScores as $result)
<li class="border border-emerald-100 rounded-xl px-3 py-2">
<p class="text-sm font-medium text-emerald-800">{{ $result->exam?->title }}</p>
<p class="text-xs text-emerald-400">
                                {{ $result->exam?->subject?->name ?? '-' }} • Nilai: {{ $result->score }}
                            </p>
                        </li>
                    @empty
<li class="text-sm text-emerald-400">Belum ada nilai ujian matakuliah.</li>
                    @endforelse
                </ul>
            </div>
        </div>
    </div>
@endsection

