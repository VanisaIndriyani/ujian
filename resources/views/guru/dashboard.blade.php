@extends('layouts.guru')

@section('title', 'Dashboard Guru')
@section('header', 'Ringkasan Hari Ini')

@section('content')
    {{-- Hero welcome dengan foto besar dosen --}}
    <div class="bg-white rounded-2xl shadow border border-emerald-100 p-6 mb-6 flex items-center gap-6">
        <img src="{{ $guru->profile_photo_url }}" alt="Foto Dosen" class="h-32 w-32 rounded-2xl object-cover ring-4 ring-emerald-100">
        <div>
            <p class="text-sm text-emerald-500 uppercase tracking-wide">Selamat Datang</p>
            <h2 class="text-2xl md:text-3xl font-semibold text-emerald-900">{{ $guru->name }}</h2>
            <p class="text-sm text-emerald-400">Semoga harimu menyenangkan dan produktif 🎓</p>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-6 mb-6">
        <div class="bg-white rounded-2xl shadow p-6 border border-emerald-100">
            <p class="text-sm text-emerald-500 font-medium">Mata Kuliah Diampu</p>
            <p class="text-3xl font-semibold text-emerald-900 mt-2">{{ $subjects->count() }}</p>
            <p class="text-xs text-emerald-400 mt-1">Total kelas aktif</p>
        </div>
        <div class="bg-white rounded-2xl shadow p-6 border border-emerald-100">
            <p class="text-sm text-emerald-500 font-medium">Absensi Hari Ini</p>
            <p class="text-3xl font-semibold text-emerald-900 mt-2">{{ $todayAttendance->count() }}</p>
            <p class="text-xs text-emerald-400 mt-1">Catatan kehadiran terbaru</p>
        </div>
        <div class="bg-white rounded-2xl shadow p-6 border border-emerald-100">
            <p class="text-sm text-emerald-500 font-medium">Tugas Aktif</p>
            <p class="text-3xl font-semibold text-emerald-900 mt-2">{{ $latestAssignments->count() }}</p>
            <p class="text-xs text-emerald-400 mt-1">Dalam 5 entri terakhir</p>
        </div>
        <div class="bg-white rounded-2xl shadow p-6 border border-emerald-100">
    <p class="text-sm text-emerald-500 font-medium">Ujian Matakuliah Terjadwal</p>
            <p class="text-3xl font-semibold text-emerald-900 mt-2">{{ $upcomingExams->count() }}</p>
            <p class="text-xs text-emerald-400 mt-1">7 hari ke depan</p>
        </div>
    </div>

    <div class="grid grid-cols-1 xl:grid-cols-3 gap-6">
        <div class="xl:col-span-2 bg-white rounded-2xl shadow border border-emerald-100 p-6">
            <h2 class="text-lg font-semibold text-emerald-900 mb-4 flex items-center gap-2">
                <span>🧑‍🏫</span> Daftar Mata Kuliah
            </h2>
            <div class="grid sm:grid-cols-2 gap-4">
                @forelse ($subjects as $subject)
                    <div class="border border-emerald-100 rounded-xl p-4">
                        <p class="text-sm text-emerald-500 uppercase tracking-wide">{{ $subject->code }}</p>
                        <p class="text-base font-semibold text-emerald-900 mt-1">{{ $subject->name }}</p>
                        <p class="text-xs text-emerald-400 mt-2">Mahasiswa terdaftar: {{ $subject->students_count }}</p>
                    </div>
                @empty
                    <p class="text-sm text-emerald-400">Belum ada mata kuliah yang diampu.</p>
                @endforelse
            </div>
        </div>

        <div class="bg-white rounded-2xl shadow border border-emerald-100 p-6 space-y-6">
            <div>
                <h2 class="text-lg font-semibold text-emerald-900 flex items-center gap-2">
                    <span>📝</span> Tugas Terbaru
                </h2>
                <ul class="mt-3 space-y-3">
                    @forelse ($latestAssignments as $assignment)
                        <li class="border border-emerald-100 rounded-xl px-3 py-2">
                            <p class="text-sm font-medium text-emerald-800">{{ $assignment->title }}</p>
                            <p class="text-xs text-emerald-400">{{ $assignment->subject?->name ?? '-' }}</p>
                        </li>
                    @empty
                        <li class="text-sm text-emerald-400">Belum ada tugas.</li>
                    @endforelse
                </ul>
            </div>
            <div>
                <h2 class="text-lg font-semibold text-emerald-900 flex items-center gap-2">
            <span>🧪</span> Ujian Matakuliah Mendatang
                </h2>
                <ul class="mt-3 space-y-3">
                    @forelse ($upcomingExams as $exam)
                        <li class="border border-emerald-100 rounded-xl px-3 py-2">
                            <p class="text-sm font-medium text-emerald-800">{{ $exam->title }}</p>
                            <p class="text-xs text-emerald-400">
                                {{ $exam->subject?->name ?? '-' }} • {{ optional($exam->start_at)->format('d M Y H:i') }}
                            </p>
                        </li>
                    @empty
            <li class="text-sm text-emerald-400">Belum ada jadwal ujian matakuliah.</li>
                    @endforelse
                </ul>
            </div>
        </div>
    </div>
@endsection

