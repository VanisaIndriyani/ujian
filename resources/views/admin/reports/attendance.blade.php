@extends('layouts.admin')

@section('title', 'Laporan Absensi')
@section('header', 'Laporan Absensi')

@section('content')
    <div class="flex flex-col gap-6">
        <div class="flex flex-wrap items-center justify-between gap-4">
            <div>
                <h2 class="text-lg font-semibold text-emerald-900">Laporan Kehadiran Mahasiswa</h2>
                <p class="text-sm text-emerald-500">Unduh laporan absensi dalam format PDF atau Excel.</p>
            </div>
            <div class="flex flex-wrap gap-2">
                <a href="{{ route('admin.reports.attendance', array_merge(request()->query(), ['format' => 'pdf'])) }}" class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-emerald-600 text-white text-sm font-medium shadow hover:bg-emerald-700">
                    Unduh PDF
                </a>
                <a href="{{ route('admin.reports.attendance', array_merge(request()->query(), ['format' => 'excel'])) }}" class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-emerald-100 text-emerald-600 text-sm font-medium shadow hover:bg-emerald-200">
                    Unduh Excel
                </a>
            </div>
        </div>

        <form method="GET" action="{{ route('admin.reports.attendance') }}" class="bg-white border border-emerald-100 rounded-2xl shadow p-4">
            <div class="grid grid-cols-1 md:grid-cols-6 gap-3">
                <div>
                    <label class="block text-xs font-medium text-emerald-700">Dari Tanggal</label>
                    <input type="date" name="start_date" value="{{ $startDate }}" class="mt-1 w-full rounded-lg border-emerald-200 focus:ring-emerald-500 focus:border-emerald-500">
                </div>
                <div>
                    <label class="block text-xs font-medium text-emerald-700">Sampai Tanggal</label>
                    <input type="date" name="end_date" value="{{ $endDate }}" class="mt-1 w-full rounded-lg border-emerald-200 focus:ring-emerald-500 focus:border-emerald-500">
                </div>
                <div>
                    <label class="block text-xs font-medium text-emerald-700">Mata Kuliah</label>
                    <select name="subject_id" class="mt-1 w-full rounded-lg border-emerald-200 focus:ring-emerald-500 focus:border-emerald-500">
                        <option value="">Semua</option>
                        @foreach ($subjects as $subject)
                            <option value="{{ $subject->id }}" @selected($subjectId == $subject->id)>{{ $subject->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-medium text-emerald-700">Jurusan</label>
                    <select name="classroom" class="mt-1 w-full rounded-lg border-emerald-200 focus:ring-emerald-500 focus:border-emerald-500">
                        <option value="">Semua</option>
                        @foreach ($classrooms as $room)
                            <option value="{{ $room }}" @selected($classroom == $room)>{{ $room }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-medium text-emerald-700">Status</label>
                    <select name="status" class="mt-1 w-full rounded-lg border-emerald-200 focus:ring-emerald-500 focus:border-emerald-500">
                        @php($statuses = ['', 'hadir', 'izin', 'sakit', 'alpa'])
                        @foreach ($statuses as $st)
                            <option value="{{ $st }}" @selected(($status ?? '') === $st)>{{ $st === '' ? 'Semua' : ucfirst($st) }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-medium text-emerald-700">Kata Kunci</label>
                    <input type="text" name="q" value="{{ $q }}" placeholder="Nama mahasiswa" class="mt-1 w-full rounded-lg border-emerald-200 focus:ring-emerald-500 focus:border-emerald-500">
                </div>
            </div>
            <div class="mt-3 flex gap-2">
                <button type="submit" class="px-4 py-2 rounded-lg bg-emerald-600 text-white text-sm font-medium shadow hover:bg-emerald-700">Terapkan Filter</button>
                <a href="{{ route('admin.reports.attendance') }}" class="px-4 py-2 rounded-lg bg-white border border-emerald-200 text-emerald-700 text-sm font-medium shadow hover:bg-emerald-50">Reset</a>
            </div>
        </form>

        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div class="p-4 bg-white border border-emerald-100 rounded-2xl shadow">
                <p class="text-sm text-emerald-600">Total Catatan</p>
                <p class="text-2xl font-semibold text-emerald-900">{{ $total }}</p>
            </div>
            <div class="p-4 bg-white border border-emerald-100 rounded-2xl shadow">
                <p class="text-sm text-emerald-600">Hadir</p>
                <p class="text-2xl font-semibold text-emerald-900">{{ $statusCounts['hadir'] ?? 0 }}</p>
            </div>
            <div class="p-4 bg-white border border-emerald-100 rounded-2xl shadow">
                <p class="text-sm text-emerald-600">Persentase Hadir</p>
                <p class="text-2xl font-semibold text-emerald-900">{{ $presentPct }}%</p>
            </div>
            <div class="p-4 bg-white border border-emerald-100 rounded-2xl shadow">
                <p class="text-sm text-emerald-600">Tidak Hadir (Alpa)</p>
                <p class="text-2xl font-semibold text-emerald-900">{{ $statusCounts['alpa'] ?? 0 }}</p>
            </div>
        </div>

        <div class="bg-white border border-emerald-100 rounded-2xl shadow p-4">
            <p class="text-sm font-semibold text-emerald-900 mb-2">Tren Absensi</p>
            <canvas id="attendanceChart" height="120"></canvas>
        </div>

        <div class="bg-white border border-emerald-100 rounded-2xl shadow overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-emerald-100 text-sm">
                    <thead class="bg-emerald-50/60 text-emerald-600 uppercase text-xs tracking-wider">
                        <tr>
                            <th class="px-4 py-3 text-left">Tanggal</th>
                            <th class="px-4 py-3 text-left">Mahasiswa</th>
                            <th class="px-4 py-3 text-left">Jurusan</th>
                            <th class="px-4 py-3 text-left">Mata Kuliah</th>
                            <th class="px-4 py-3 text-left">Status</th>
                            <th class="px-4 py-3 text-left">Pencatat</th>
                            <th class="px-4 py-3 text-left">Catatan</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-emerald-50">
                        @forelse ($records as $record)
                            <tr class="hover:bg-emerald-50/40">
                                <td class="px-4 py-3 text-emerald-600">{{ $record->attendance_date->format('d M Y') }}</td>
                                <td class="px-4 py-3 text-emerald-600">{{ $record->student?->name ?? '—' }}</td>
                                <td class="px-4 py-3 text-emerald-600">{{ $record->student?->classroom ?? '—' }}</td>
                                <td class="px-4 py-3 text-emerald-600">{{ $record->subject?->name ?? '—' }}</td>
                                <td class="px-4 py-3 text-emerald-600">{{ strtoupper($record->status) }}</td>
                                <td class="px-4 py-3 text-emerald-600">{{ $record->recorder?->name ?? '—' }}</td>
                                <td class="px-4 py-3 text-emerald-600">{{ $record->notes ?? '—' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-4 py-6 text-center text-emerald-400">Belum ada data absensi.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
                <div class="p-3 border-t border-emerald-100 bg-emerald-50/40">
                    {{ $records->links() }}
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', () => {
        const ctx = document.getElementById('attendanceChart');
        if (!ctx || !window.Chart) return;
        const labels = @json(collect($chartData)->pluck('date'));
        const data = @json(collect($chartData)->pluck('total'));
        new Chart(ctx, {
            type: 'line',
            data: {
                labels,
                datasets: [{
                    label: 'Jumlah Catatan',
                    data,
                    borderColor: '#059669',
                    backgroundColor: 'rgba(5, 150, 105, 0.15)',
                    tension: 0.3,
                    fill: true,
                }]
            },
            options: {
                plugins: { legend: { display: false } },
                scales: {
                    x: { grid: { display: false } },
                    y: { grid: { color: 'rgba(5, 150, 105, 0.1)' }, beginAtZero: true }
                }
            }
        });
    });
</script>
@endpush

