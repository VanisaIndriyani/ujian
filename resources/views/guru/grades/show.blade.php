@extends('layouts.guru')

@section('title', 'Input Nilai Siswa')
@section('header', 'Input Nilai & Bobot Siswa')

@section('content')
    @if (session('status'))
        <div id="status-toast" class="fixed top-4 right-4 z-50 rounded-xl border border-emerald-200 bg-emerald-50 text-emerald-800 shadow-lg px-4 py-3 flex items-start gap-3">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-5 h-5 text-emerald-600"><path d="M12 2a10 10 0 1 0 0 20 10 10 0 0 0 0-20Zm-1 14-4-4 1.4-1.4L11 12.2l4.6-4.6L17 9l-6 7Z"/></svg>
            <div>
                <p class="font-semibold">Berhasil</p>
                <p class="text-sm">{{ session('status') }}</p>
            </div>
            <button onclick="document.getElementById('status-toast')?.remove()" class="ml-3 text-emerald-600 hover:text-emerald-800">&times;</button>
        </div>
        <script>
            setTimeout(() => {
                const el = document.getElementById('status-toast');
                if (el) el.style.display = 'none';
            }, 3500);
        </script>
    @endif
    <div class="bg-white border border-emerald-100 rounded-2xl shadow overflow-hidden">
        <div class="px-4 py-3 border-b border-emerald-100">
            <p class="text-sm font-semibold text-emerald-800">Tambah/Update Nilai Siswa</p>
            <p class="text-xs text-emerald-500">Sesuaikan bobot: Absen 10%, Praktikum 10%, Tugas 20%, UTS 30%, UAS 30% (dapat diubah)</p>
        </div>
        

        <form method="POST" action="{{ route('guru.grades.student.save', ['student' => $student->id]) }}" class="p-4 space-y-4 text-sm">
            @csrf

            <div class="grid sm:grid-cols-3 gap-4">
                <div>
                    <label class="text-emerald-600 text-xs">Nama Lengkap Siswa</label>
                    <input type="text" value="{{ $student->name }}" readonly class="w-full rounded-lg border border-emerald-200 px-3 py-2 bg-emerald-50">
                </div>
                <div>
                    <label class="text-emerald-600 text-xs">Kelas</label>
                    <input type="text" value="{{ $student->classroom ?? '—' }}" readonly class="w-full rounded-lg border border-emerald-200 px-3 py-2 bg-emerald-50">
                </div>
                <div>
                    <label class="text-emerald-600 text-xs">Mata Kuliah</label>
                    <select name="subject_id" class="w-full rounded-lg border border-emerald-200 px-3 py-2">
                        @foreach ($subjects as $subject)
                            <option value="{{ $subject->id }}" {{ (int) $subjectId === (int) $subject->id ? 'selected' : '' }}>{{ $subject->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="grid sm:grid-cols-2 gap-4">
                <div class="space-y-3">
                    <p class="text-emerald-700 font-medium">Input Nilai (0-100)</p>
                    <div class="grid grid-cols-2 gap-3 items-center">
                        <label class="text-emerald-600">Absensi</label>
                        <input type="number" name="attendance_score" min="0" max="100" step="0.01" value="{{ $components['attendance'] ?? '' }}" class="rounded-lg border border-emerald-200 px-3 py-2">

                        <label class="text-emerald-600">Praktikum</label>
                        <input type="number" name="praktikum_score" min="0" max="100" step="0.01" value="{{ $components['praktikum'] ?? '' }}" class="rounded-lg border border-emerald-200 px-3 py-2">

                        <label class="text-emerald-600">Tugas</label>
                        <input type="number" name="tugas_score" min="0" max="100" step="0.01" value="{{ $components['tugas'] ?? '' }}" class="rounded-lg border border-emerald-200 px-3 py-2">

                        <label class="text-emerald-600">UTS</label>
                        <input type="number" name="uts_score" min="0" max="100" step="0.01" value="{{ $components['uts'] ?? '' }}" class="rounded-lg border border-emerald-200 px-3 py-2">

                        <label class="text-emerald-600">UAS</label>
                        <input type="number" name="uas_score" min="0" max="100" step="0.01" value="{{ $components['uas'] ?? '' }}" class="rounded-lg border border-emerald-200 px-3 py-2">
                    </div>
                </div>

                <div class="space-y-3">
                    <p class="text-emerald-700 font-medium">Bobot (%)</p>
                    <div class="grid grid-cols-2 gap-3 items-center">
                        <label class="text-emerald-600">Absen</label>
                        <input type="number" name="w_absen" min="0" max="100" step="1" value="{{ $weights['wAbsen'] ?? 10 }}" class="rounded-lg border border-emerald-200 px-3 py-2">

                        <label class="text-emerald-600">Praktikum</label>
                        <input type="number" name="w_praktikum" min="0" max="100" step="1" value="{{ $weights['wPraktikum'] ?? 10 }}" class="rounded-lg border border-emerald-200 px-3 py-2">

                        <label class="text-emerald-600">Tugas</label>
                        <input type="number" name="w_tugas" min="0" max="100" step="1" value="{{ $weights['wTugas'] ?? 20 }}" class="rounded-lg border border-emerald-200 px-3 py-2">

                        <label class="text-emerald-600">UTS</label>
                        <input type="number" name="w_uts" min="0" max="100" step="1" value="{{ $weights['wUts'] ?? 30 }}" class="rounded-lg border border-emerald-200 px-3 py-2">

                        <label class="text-emerald-600">UAS</label>
                        <input type="number" name="w_uas" min="0" max="100" step="1" value="{{ $weights['wUas'] ?? 30 }}" class="rounded-lg border border-emerald-200 px-3 py-2">
                    </div>
                    <div class="rounded-lg bg-emerald-50 border border-emerald-100 p-3 text-emerald-700">
                        Total Bobot: <strong>{{ ($weights['wAbsen'] ?? 0) + ($weights['wPraktikum'] ?? 0) + ($weights['wTugas'] ?? 0) + ($weights['wUts'] ?? 0) + ($weights['wUas'] ?? 0) }}%</strong>
                    </div>
                </div>
            </div>

            <div class="grid sm:grid-cols-4 gap-4 items-center">
                <div class="rounded-lg bg-emerald-50 border border-emerald-100 p-3 text-emerald-700">
                    Nilai Akhir: <strong>{{ $finalScore ?? '—' }}</strong>
                </div>
                <div class="rounded-lg bg-emerald-50 border border-emerald-100 p-3 text-emerald-700">
                    Predikat: <strong>{{ $predikat }}</strong>
                </div>
                <div class="rounded-lg bg-emerald-50 border border-emerald-100 p-3 text-emerald-700">
                    Keterangan: <strong>{{ $keterangan ?? '—' }}</strong>
                </div>
                <div class="text-right">
                    <button class="px-4 py-2 rounded-lg bg-emerald-600 text-white hover:bg-emerald-700">Simpan Data</button>
                </div>
            </div>
        </form>
    </div>

    <div class="mt-4">
        <a href="{{ route('guru.grades.index', ['subject_id' => $subjectId]) }}" class="text-emerald-600 hover:underline">&larr; Kembali ke halaman Nilai</a>
    </div>
@endsection