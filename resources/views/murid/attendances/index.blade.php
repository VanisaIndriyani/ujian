@extends('layouts.murid')

@section('title', 'Riwayat Kehadiran')
@section('header', 'Riwayat Kehadiran')

@section('content')
<div class="bg-white border border-emerald-100 rounded-2xl shadow overflow-hidden">
        <div class="px-4 py-3 border-b border-emerald-100">
            <form method="GET" action="{{ route('murid.attendances.index') }}" class="flex flex-wrap items-center gap-3 text-sm">
                <label class="text-emerald-500 font-medium">Filter Bulan</label>
                <input type="month" name="month" value="{{ $month ?? request('month') }}" class="rounded-lg border border-emerald-200 px-3 py-2 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-transparent">

                <label class="text-emerald-500 font-medium ml-2">Status</label>
                <select name="status" class="rounded-lg border border-emerald-200 px-3 py-2 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-transparent">
                    @php $statusVal = $status ?? request('status'); @endphp
                    <option value="">Semua</option>
                    <option value="hadir" {{ $statusVal === 'hadir' ? 'selected' : '' }}>Hadir</option>
                    <option value="izin" {{ $statusVal === 'izin' ? 'selected' : '' }}>Izin</option>
                    <option value="sakit" {{ $statusVal === 'sakit' ? 'selected' : '' }}>Sakit</option>
                    <option value="alpa" {{ $statusVal === 'alpa' ? 'selected' : '' }}>Alpa</option>
                </select>

                <button type="submit" class="px-3 py-2 rounded-lg bg-emerald-600 text-white font-medium shadow hover:bg-emerald-700">Terapkan</button>
                <a href="{{ route('murid.attendances.index') }}" class="px-3 py-2 rounded-lg border border-emerald-200 text-emerald-700 hover:bg-emerald-50">Reset</a>
            </form>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-purple-100 text-sm">
<thead class="bg-emerald-50/60 text-emerald-600 uppercase text-xs tracking-wider">
                    <tr>
                        <th class="px-4 py-3 text-left">Tanggal</th>
                        <th class="px-4 py-3 text-left">Mata Kuliah</th>
                        <th class="px-4 py-3 text-left">Status</th>
                       
                    </tr>
                </thead>
                <tbody class="divide-y divide-purple-50">
                    @forelse ($attendances as $attendance)
<tr class="hover:bg-emerald-50/40">
<td class="px-4 py-3 text-emerald-600">{{ $attendance->attendance_date->format('d M Y') }}</td>
<td class="px-4 py-3 text-emerald-600">{{ $attendance->subject?->name ?? '—' }}</td>
                            <td class="px-4 py-3">
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium
                                    @class([
'bg-emerald-100 text-emerald-700' => $attendance->status === 'hadir',
                                        'bg-yellow-100 text-yellow-700' => $attendance->status === 'izin',
                                        'bg-blue-100 text-blue-700' => $attendance->status === 'sakit',
                                        'bg-red-100 text-red-700' => $attendance->status === 'alpa',
                                    ])">
                                    {{ strtoupper($attendance->status) }}
                                </span>
                            </td>

                        </tr>
                    @empty
                        <tr>
<td colspan="4" class="px-4 py-6 text-center text-emerald-400">Belum ada data kehadiran.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

<div class="px-4 py-3 border-t border-emerald-100">
            {{ $attendances->links() }}
        </div>
    </div>
@endsection

