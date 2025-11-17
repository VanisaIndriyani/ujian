@extends('layouts.admin')

@section('title', 'Dashboard Admin')
@section('header', 'Dasbor Administrator')

@section('content')
    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-6 mb-6">
        <div class="bg-white rounded-2xl shadow p-6 border border-emerald-100">
            <p class="text-sm text-emerald-500 font-medium">Total Dosen</p>
            <p class="text-3xl font-semibold text-emerald-900 mt-2">{{ $guruCount }}</p>
            <p class="text-xs text-emerald-400 mt-1">Terdata aktif dalam sistem</p>
        </div>
        <div class="bg-white rounded-2xl shadow p-6 border border-emerald-100">
            <p class="text-sm text-emerald-500 font-medium">Total Mahasiswa</p>
            <p class="text-3xl font-semibold text-emerald-900 mt-2">{{ $muridCount }}</p>
            <p class="text-xs text-emerald-400 mt-1">Terhubung dengan mata kuliah</p>
        </div>
        <div class="bg-white rounded-2xl shadow p-6 border border-emerald-100">
            <p class="text-sm text-emerald-500 font-medium">Absensi Hari Ini</p>
            <p class="text-3xl font-semibold text-emerald-900 mt-2">{{ $attendanceToday }}</p>
            <p class="text-xs text-emerald-400 mt-1">Total catatan kehadiran</p>
        </div>
        <div class="bg-white rounded-2xl shadow p-6 border border-emerald-100">
            <p class="text-sm text-emerald-500 font-medium">Rata-rata Nilai</p>
            <p class="text-3xl font-semibold text-emerald-900 mt-2">{{ $averageGrade }}</p>
            <p class="text-xs text-emerald-400 mt-1">Kalkulasi keseluruhan semester</p>
        </div>
    </div>

    <div class="grid grid-cols-1 xl:grid-cols-3 gap-6">
        <div class="xl:col-span-2 bg-white rounded-2xl shadow border border-emerald-100 p-6">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-lg font-semibold text-emerald-900">Tren Absensi 7 Hari</h2>
                <span class="text-sm text-emerald-400">Data harian</span>
            </div>
            <canvas id="attendanceChart" height="150"></canvas>
        </div>

        <div class="bg-white rounded-2xl shadow border border-emerald-100 p-6 space-y-6">
            <div>
                <h2 class="text-lg font-semibold text-emerald-900 flex items-center gap-2">
                    <span>📚</span> Tugas Terbaru
                </h2>
                <ul class="mt-3 space-y-3">
                    @forelse ($recentAssignments as $assignment)
                        <li class="border border-emerald-100 rounded-xl px-3 py-2">
                            <p class="text-sm font-medium text-emerald-800">{{ $assignment->title }}</p>
                            <p class="text-xs text-emerald-400">{{ $assignment->subject?->name ?? '-' }}</p>
                        </li>
                    @empty
                        <li class="text-sm text-emerald-400">Belum ada tugas terbaru.</li>
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

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const ctx = document.getElementById('attendanceChart');
            if (!ctx) return;

            const chartData = @json($attendanceChart);

            new Chart(ctx, {
                type: 'line',
                data: {
                    labels: chartData.map(item => item.date),
                    datasets: [
                        {
                            label: 'Total Absensi',
                            data: chartData.map(item => item.total),
                            borderColor: '#059669',
                            backgroundColor: 'rgba(5, 150, 105, 0.15)',
                            tension: 0.4,
                            fill: true,
                        },
                    ],
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: {
                            display: false,
                        },
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                stepSize: 1,
                            },
                            grid: {
                                color: 'rgba(5, 150, 105, 0.1)',
                            },
                        },
                        x: {
                            grid: {
                                color: 'rgba(5, 150, 105, 0.05)',
                            },
                        },
                    },
                },
            });
        });
    </script>
@endpush

