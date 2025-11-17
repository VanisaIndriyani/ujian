@extends('layouts.guru')

@section('title', 'Nilai Siswa')
@section('header', 'Nilai Siswa per Mata Kuliah')

@section('content')
    <!-- Filter utama di bagian atas: mata kuliah, jurusan, bobot, search, export -->
    <div class="mb-4 bg-white border border-emerald-100 rounded-2xl shadow overflow-hidden">
        <div class="px-4 py-3">
            <form method="GET" action="{{ route('guru.grades.index') }}" class="flex flex-wrap items-center gap-3 text-sm">
                
                <label class="text-emerald-500 font-medium">Cari</label>
                <input type="text" name="q" value="{{ $q ?? request('q') }}" placeholder="Nama/Kelas/Mata Kuliah/Predikat" class="rounded-lg border border-emerald-200 px-3 py-2 w-64 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-transparent">
               
                <a href="{{ route('guru.grades.summary', ['q' => ($q ?? request('q'))]) }}" class="inline-flex items-center gap-2 px-3 py-2 rounded-lg bg-emerald-50 text-emerald-700 hover:bg-emerald-100" aria-label="Ringkasan">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-4 h-4">
                        <path d="M4 6h16v2H4V6Zm0 5h16v2H4v-2Zm0 5h16v2H4v-2Z"/>
                    </svg>
                    <span>Ringkasan</span>
                </a>
            </form>
        </div>
    </div>
    

    <!-- Daftar Siswa dengan tombol Show -->
    <div class="mt-6 bg-white border border-emerald-100 rounded-2xl shadow overflow-hidden">
        <div class="px-4 py-3 border-b border-emerald-100">
            <p class="text-sm font-semibold text-emerald-800">Daftar Siswa</p>
            <p class="text-xs text-emerald-500">Klik "Input Nilai" untuk mengisi nilai komponen</p>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-emerald-100 text-sm">
                <thead class="bg-emerald-50/60 text-emerald-600 uppercase text-xs tracking-wider">
                    <tr>
                        <th class="px-4 py-3 text-left">Mahasiswa</th>
                        <th class="px-4 py-3 text-left">Jurusan</th>
                        <th class="px-4 py-3 text-left">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-emerald-50">
                    @forelse ($students ?? [] as $stu)
                        <tr class="hover:bg-emerald-50/40">
                            <td class="px-4 py-3 text-emerald-600">{{ $stu->name }}</td>
                            <td class="px-4 py-3 text-emerald-600">{{ $stu->classroom ?? '—' }}</td>
                            <td class="px-4 py-3">
                                <div class="flex items-center gap-2">
                                    <a href="{{ route('guru.grades.student.show', ['student' => $stu->id, 'subject_id' => ($subjectId ?? request('subject_id'))]) }}" class="inline-flex items-center justify-center w-9 h-9 rounded-xl border border-emerald-200 text-emerald-600 bg-white hover:bg-emerald-50" title="Input Nilai" aria-label="Input Nilai">
                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-5 h-5">
                                            <path d="M12 5c-7.633 0-11 7-11 7s3.367 7 11 7 11-7 11-7-3.367-7-11-7Zm0 12a5 5 0 1 1 0-10 5 5 0 0 1 0 10Z"/>
                                        </svg>
                                    </a>
                                    <a href="{{ route('guru.grades.student.export', ['student' => $stu->id, 'subject_id' => ($subjectId ?? request('subject_id')), 'w_absen' => 10, 'w_praktikum' => ($weights['w_praktikum'] ?? 20), 'w_tugas' => ($weights['w_tugas'] ?? 20), 'w_uts' => ($weights['w_uts'] ?? 30), 'w_uas' => ($weights['w_uas'] ?? 30)]) }}" class="inline-flex items-center justify-center w-9 h-9 rounded-xl border border-emerald-200 text-emerald-600 bg-white hover:bg-emerald-50" title="Export PDF" aria-label="Export PDF">
                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-5 h-5">
                                            <path d="M4 18h16v2H4v-2Zm8-14v10l-4-4 1.5-1.5L12 11l2.5-2.5L16 10l-4 4V4Z"/>
                                        </svg>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="px-4 py-6 text-center text-emerald-400">Belum ada siswa untuk filter ini.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
            <div class="px-4 py-3">{{ ($students ?? null)?->links() }}</div>
        </div>
    </div>

    <!-- Seksi ringkasan dihapus sesuai permintaan; filter dipindah ke atas -->
@endsection

