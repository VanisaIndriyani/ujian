@extends('layouts.murid')

@section('title', 'Ujian Matakuliah')
@section('header', 'Daftar Ujian Matakuliah')

@section('content')
<div class="bg-white border border-emerald-100 rounded-2xl shadow overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-purple-100 text-sm">
<thead class="bg-emerald-50/60 text-emerald-600 uppercase text-xs tracking-wider">
                    <tr>
<th class="px-4 py-3 text-left">Ujian Matakuliah</th>
                        <th class="px-4 py-3 text-left">Mata Kuliah</th>
                        <th class="px-4 py-3 text-left">Jenis</th>
                        <th class="px-4 py-3 text-left">Jadwal</th>
                        <th class="px-4 py-3 text-left">Nilai Anda</th>
                       
                    </tr>
                </thead>
                <tbody class="divide-y divide-purple-50">
                    @forelse ($exams as $exam)
                        @php
                            $result = $exam->results->first();
                        @endphp
<tr class="hover:bg-emerald-50/40">
<td class="px-4 py-3 font-medium text-emerald-900">{{ $exam->title }}</td>
<td class="px-4 py-3 text-emerald-600">{{ $exam->subject?->name ?? '—' }}</td>
<td class="px-4 py-3 text-emerald-600">{{ $exam->type ?? '—' }}</td>
<td class="px-4 py-3 text-emerald-600">
                                {{ optional($exam->start_at)->format('d M Y H:i') ?? '—' }}<br>
<span class="text-xs text-emerald-400">s/d {{ optional($exam->end_at)->format('d M Y H:i') ?? '—' }}</span>
                            </td>
<td class="px-4 py-3 text-emerald-600">
    @if(!$result || !$result->submitted_at)
        <a href="{{ route('murid.exams.show', $exam) }}" class="inline-flex items-center gap-2 px-3 py-2 rounded-lg bg-emerald-600 text-white text-xs hover:bg-emerald-700">
            Kerjakan
        </a>
    @elseif($result->score !== null)
        {{ number_format($result->score, 2) }}
    @else
        Menunggu nilai
    @endif
</td>
                           
                        </tr>
                    @empty
                        <tr>
<td colspan="5" class="px-4 py-6 text-center text-emerald-400">Belum ada ujian matakuliah tersedia.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

<div class="px-4 py-3 border-t border-emerald-100">
            {{ $exams->links() }}
        </div>
    </div>
@endsection

