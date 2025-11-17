@extends('layouts.admin')

@section('title', 'Laporan Nilai')
@section('header', 'Laporan Nilai Semester')

@section('content')
    <div class="flex flex-col gap-6">
        <div class="flex flex-wrap items-center justify-between gap-4">
            <div>
                <h2 class="text-lg font-semibold text-emerald-900">Laporan Nilai Mahasiswa</h2>
                <p class="text-sm text-emerald-500">Unduh rekap nilai semester dalam format PDF atau Excel.</p>
            </div>
            <div class="flex flex-wrap gap-2">
                <a href="{{ route('admin.reports.grades', ['format' => 'pdf']) }}" class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-emerald-600 text-white text-sm font-medium shadow hover:bg-emerald-700">
                    Unduh PDF
                </a>
                <a href="{{ route('admin.reports.grades', ['format' => 'excel']) }}" class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-emerald-100 text-emerald-600 text-sm font-medium shadow hover:bg-emerald-200">
                    Unduh Excel
                </a>
            </div>
        </div>

        <div class="bg-white border border-emerald-100 rounded-2xl shadow overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-emerald-100 text-sm">
                    <thead class="bg-emerald-50/60 text-emerald-600 uppercase text-xs tracking-wider">
                        <tr>
                            <th class="px-4 py-3 text-left">Mahasiswa</th>
                            <th class="px-4 py-3 text-left">Kelas</th>
<th class="px-4 py-3 text-left">Mata Kuliah</th>
                            <th class="px-4 py-3 text-left">Semester</th>
                            <th class="px-4 py-3 text-left">Nilai</th>
                            <th class="px-4 py-3 text-left">Catatan</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-emerald-50">
                        @forelse ($records as $record)
                            <tr class="hover:bg-emerald-50/40">
                                <td class="px-4 py-3 text-emerald-600">{{ $record->student?->name ?? '—' }}</td>
                                <td class="px-4 py-3 text-emerald-600">{{ $record->student?->classroom ?? '—' }}</td>
                                <td class="px-4 py-3 text-emerald-600">{{ $record->subject?->name ?? '—' }}</td>
                                <td class="px-4 py-3 text-emerald-600">{{ $record->semester }}</td>
                                <td class="px-4 py-3 text-emerald-600">{{ number_format($record->score, 2) }}</td>
                                <td class="px-4 py-3 text-emerald-600">{{ $record->notes ?? '—' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-4 py-6 text-center text-emerald-400">Belum ada data nilai.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection

