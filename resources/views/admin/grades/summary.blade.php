@extends('layouts.admin')

@section('title', 'Ringkasan Nilai')
@section('header', 'Ringkasan Nilai Keseluruhan')

@section('content')
    <div class="mb-4 bg-white border border-emerald-100 rounded-2xl shadow overflow-hidden">
        <div class="px-4 py-3">
            <form method="GET" action="{{ route('admin.grades.summary') }}" class="flex flex-wrap items-center gap-3 text-sm">
                <label class="text-emerald-500 font-medium">Cari</label>
                <input type="text" name="q" value="{{ $q ?? request('q') }}" placeholder="Cari nama/jurusan/mata kuliah/predikat" class="rounded-lg border border-emerald-200 px-3 py-2 w-64">
                <label class="text-emerald-500 font-medium">Mata Kuliah</label>
                <select name="subject_id" class="rounded-lg border border-emerald-200 px-3 py-2">
                    <option value="">Semua</option>
                    @foreach ($subjects as $s)
                        <option value="{{ $s->id }}" {{ (string)($subjectId ?? request('subject_id')) === (string)$s->id ? 'selected' : '' }}>{{ $s->name }}</option>
                    @endforeach
                </select>
                <label class="text-emerald-500 font-medium">Kelas</label>
                <select name="classroom" class="rounded-lg border border-emerald-200 px-3 py-2">
                    <option value="">Semua</option>
                    @foreach ($classrooms as $c)
                        <option value="{{ $c }}" {{ (string)($classroom ?? request('classroom')) === (string)$c ? 'selected' : '' }}>{{ $c }}</option>
                    @endforeach
                </select>
                <button class="px-3 py-2 rounded-lg bg-emerald-100 text-emerald-600 hover:bg-emerald-200">Terapkan</button>
                <a href="{{ route('admin.grades.index', request()->query()) }}" class="px-3 py-2 rounded-lg bg-emerald-50 text-emerald-700 hover:bg-emerald-100">Kembali</a>

                <span class="ml-auto"></span>
                <a href="{{ route('admin.grades.export.pdf', [
                    'subject_id' => request('subject_id'),
                    'classroom' => request('classroom'),
                    'q' => request('q'),
                    'w_uts' => request('w_uts', 30),
                    'w_uas' => request('w_uas', 30),
                    'w_tugas' => request('w_tugas', 20),
                    'w_praktikum' => request('w_praktikum', 20),
                ]) }}" class="inline-flex items-center gap-2 px-3 py-2 rounded-lg bg-emerald-600 text-white hover:bg-emerald-700" title="Export PDF" aria-label="Export PDF" target="_blank">
                    <i class="fa-solid fa-file-pdf"></i>
                    <span>Export PDF</span>
                </a>
                <a href="{{ route('admin.grades.export.excel', [
                    'subject_id' => request('subject_id'),
                    'classroom' => request('classroom'),
                    'q' => request('q'),
                    'w_uts' => request('w_uts', 30),
                    'w_uas' => request('w_uas', 30),
                    'w_tugas' => request('w_tugas', 20),
                    'w_praktikum' => request('w_praktikum', 20),
                ]) }}" class="inline-flex items-center gap-2 px-3 py-2 rounded-lg bg-emerald-100 text-emerald-700 hover:bg-emerald-200" title="Export Excel" aria-label="Export Excel">
                    <i class="fa-solid fa-file-excel"></i>
                    <span>Export Excel</span>
                </a>
            </form>
        </div>
    </div>

    <div class="bg-white border border-emerald-100 rounded-2xl shadow overflow-hidden">
        <div class="px-4 py-3 border-b border-emerald-100">
            <p class="text-sm font-semibold text-emerald-800">Data Nilai Siswa</p>
            <p class="text-xs text-emerald-500">Ringkasan nilai komponen dan hasil akhir sesuai filter & bobot</p>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-emerald-100 text-sm">
                <thead class="bg-emerald-50/60 text-emerald-600 uppercase text-xs tracking-wider">
                    <tr>
                        <th class="px-4 py-3 text-left">Mahasiswa</th>
                        <th class="px-4 py-3 text-left">Jurusan</th>
                        <th class="px-4 py-3 text-left">Mata Kuliah</th>
                        <th class="px-4 py-3 text-left">Nilai UTS</th>
                        <th class="px-4 py-3 text-left">Nilai UAS</th>
                        <th class="px-4 py-3 text-left">Nilai Tugas</th>
                        <th class="px-4 py-3 text-left">Nilai Praktikum</th>
                        <th class="px-4 py-3 text-left">Rata-rata</th>
                        <th class="px-4 py-3 text-left">Predikat</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-emerald-50">
                    @forelse ($gradeSummary as $row)
                        <tr class="hover:bg-emerald-50/40">
                            <td class="px-4 py-3 text-emerald-700">{{ $row->student_name }}</td>
                            <td class="px-4 py-3 text-emerald-700">{{ $row->classroom }}</td>
                            <td class="px-4 py-3 text-emerald-700">{{ $row->subject }}</td>
                            <td class="px-4 py-3 text-emerald-700">{{ $row->uts }}</td>
                            <td class="px-4 py-3 text-emerald-700">{{ $row->uas }}</td>
                            <td class="px-4 py-3 text-emerald-700">{{ $row->tugas }}</td>
                            <td class="px-4 py-3 text-emerald-700">{{ $row->praktikum }}</td>
                            <td class="px-4 py-3 text-emerald-700">{{ $row->avg }}</td>
                            <td class="px-4 py-3 text-emerald-700">{{ $row->predikat }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="px-4 py-6 text-center text-emerald-400">Tidak ada data untuk filter ini.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection