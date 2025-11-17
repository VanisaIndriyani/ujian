@extends('layouts.murid')

@section('title', 'Nilai')
@section('header', 'Nilai Semester Anda')

@section('content')
<div class="bg-white border border-emerald-100 rounded-2xl shadow overflow-hidden">
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-emerald-100 text-sm">
            <thead class="bg-emerald-50/60 text-emerald-600 uppercase text-xs tracking-wider">
                <tr>
                    <th class="px-4 py-3 text-left">Mata Kuliah</th>
                    <th class="px-4 py-3 text-left">Semester</th>
                    <th class="px-4 py-3 text-left">Nilai UTS</th>
                    <th class="px-4 py-3 text-left">Nilai UAS</th>
                    <th class="px-4 py-3 text-left">Nilai Tugas</th>
                    <th class="px-4 py-3 text-left">Nilai Praktikum</th>
                    <th class="px-4 py-3 text-left">Rata-rata</th>
                    <th class="px-4 py-3 text-left">Predikat</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-emerald-50">
                @forelse ($grades as $grade)
                    @php
                        $cfg = json_decode($grade->notes ?? '[]', true);
                        if (!is_array($cfg)) { $cfg = []; }
                        $w = [
                            'absen' => (float)($cfg['w_absen'] ?? 10),
                            'praktikum' => (float)($cfg['w_praktikum'] ?? 10),
                            'tugas' => (float)($cfg['w_tugas'] ?? 20),
                            'uts' => (float)($cfg['w_uts'] ?? 30),
                            'uas' => (float)($cfg['w_uas'] ?? 30),
                        ];
                    @endphp
                    <tr class="hover:bg-emerald-50/40">
                        <td class="px-4 py-3 text-emerald-700">{{ $grade->subject?->name ?? '—' }}</td>
                        <td class="px-4 py-3 text-emerald-700">{{ $grade->semester }}</td>
                        <td class="px-4 py-3 text-emerald-700">{{ isset($cfg['uts_score']) ? number_format((float)$cfg['uts_score'], 2) : '—' }}</td>
                        <td class="px-4 py-3 text-emerald-700">{{ isset($cfg['uas_score']) ? number_format((float)$cfg['uas_score'], 2) : '—' }}</td>
                        <td class="px-4 py-3 text-emerald-700">{{ isset($cfg['tugas_score']) ? number_format((float)$cfg['tugas_score'], 2) : '—' }}</td>
                        <td class="px-4 py-3 text-emerald-700">{{ isset($cfg['praktikum_score']) ? number_format((float)$cfg['praktikum_score'], 2) : '—' }}</td>
                        <td class="px-4 py-3 text-emerald-900 font-semibold">{{ is_null($grade->score) ? '—' : number_format($grade->score, 2) }}</td>
                        <td class="px-4 py-3 text-emerald-700">{{ $cfg['predikat'] ?? '—' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="px-4 py-6 text-center text-emerald-400">Belum ada data nilai untuk ditampilkan.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="px-4 py-3 border-t border-emerald-100">
        {{ $grades->links() }}
    </div>
</div>
@endsection

