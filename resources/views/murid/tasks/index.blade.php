@extends('layouts.murid')

@section('title', 'Tugas')
@section('header', 'Daftar Tugas')

@section('content')
    <div class="flex flex-col gap-6">
        <div class="flex flex-wrap items-center justify-between gap-4">
            <div>
                <h2 class="text-lg font-semibold text-emerald-900">Tugas dari dosen</h2>
                <p class="text-sm text-emerald-500">Kerjakan tugas sebelum tenggat waktu ya!</p>
            </div>
            <div class="flex items-center gap-2">
                @php $status = $status ?? request('status'); @endphp
                <a href="{{ route('murid.tasks.index') }}"
                   class="px-3 py-1 rounded-full border text-xs font-medium {{ !$status ? 'bg-emerald-600 text-white border-emerald-600' : 'border-emerald-200 text-emerald-700 hover:bg-emerald-50' }}">
                    Semua
                </a>
                <a href="{{ route('murid.tasks.index', ['status' => 'sudah']) }}"
                   class="px-3 py-1 rounded-full border text-xs font-medium {{ $status === 'sudah' ? 'bg-emerald-600 text-white border-emerald-600' : 'border-emerald-200 text-emerald-700 hover:bg-emerald-50' }}">
                    Sudah dikumpulkan
                </a>
                <a href="{{ route('murid.tasks.index', ['status' => 'belum']) }}"
                   class="px-3 py-1 rounded-full border text-xs font-medium {{ $status === 'belum' ? 'bg-emerald-600 text-white border-emerald-600' : 'border-emerald-200 text-emerald-700 hover:bg-emerald-50' }}">
                    Belum dikumpulkan
                </a>
            </div>
        </div>

<div class="bg-white border border-emerald-100 rounded-2xl shadow overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-purple-100 text-sm">
<thead class="bg-emerald-50/60 text-emerald-600 uppercase text-xs tracking-wider">
                        <tr>
                            <th class="px-4 py-3 text-left">Judul</th>
                            <th class="px-4 py-3 text-left">Mata Kuliah</th>
                            <th class="px-4 py-3 text-left">Tenggat</th>
                            <th class="px-4 py-3 text-left">Status</th>
                            <th class="px-4 py-3 text-left">Nilai</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-purple-50">
                        @forelse ($assignments as $assignment)
                            @php
                                // Prioritas: ambil submission yang sudah dinilai (punya score) dulu
                                $scoredSub = $scoredSubmissions[$assignment->id] ?? collect();
                                $submissionWithScore = $scoredSub->first();
                                // Jika tidak ada yang sudah dinilai, ambil submission terbaru dari relasi
                                $submission = $submissionWithScore ?? $assignment->submissions->where('student_id', auth()->id())->first();
                                $isLate = $assignment->due_at && now()->gt($assignment->due_at);
                            @endphp
<tr class="hover:bg-emerald-50/40">
<td class="px-4 py-3 font-medium text-emerald-900">{{ $assignment->title }}</td>
<td class="px-4 py-3 text-emerald-600">{{ $assignment->subject?->name ?? '—' }}</td>
<td class="px-4 py-3 text-emerald-600">{{ optional($assignment->due_at)->format('d M Y H:i') ?? 'Tidak ditentukan' }}</td>
                                <td class="px-4 py-3">
                                    @if ($submission)
                                        <span class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-emerald-100 text-emerald-700 text-xs font-medium">
                                            <i class="fa-solid fa-check-circle"></i>
                                            <span>Sudah dikumpulkan</span>
                                        </span>
                                    @elseif ($isLate)
                                        <span class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-gray-200 text-gray-700 text-xs font-medium">
                                            <i class="fa-solid fa-ban"></i>
                                            <span>Lewat tenggat</span>
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-red-100 text-red-600 text-xs font-medium">
                                            <i class="fa-solid fa-upload"></i>
                                            <span>Belum dikumpulkan</span>
                                        </span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-emerald-600">
                                    @if (!$submission)
                                        @if ($isLate)
                                            <span class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-gray-300 text-gray-600 text-xs font-medium" title="Pengumpulan ditutup">
                                                <i class="fa-solid fa-lock"></i>
                                                <span>Ditutup</span>
                                            </span>
                                        @else
                                            <a href="{{ route('murid.tasks.create', ['assignment_id' => $assignment->id]) }}" title="Kumpulkan Tugas" aria-label="Kumpulkan Tugas" class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-emerald-600 text-white text-xs font-medium hover:bg-emerald-700">
                                                <i class="fa-solid fa-upload"></i>
                                                <span>Kumpulkan</span>
                                            </a>
                                        @endif
                                    @elseif ($submission->score !== null)
                                        {{ number_format($submission->score, 2) }}
                                    @else
                                        Menunggu nilai
                                    @endif
                                </td>
                                
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-4 py-6 text-center text-emerald-400">Belum ada tugas.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

<div class="px-4 py-3 border-t border-emerald-100">
                {{ $assignments->links() }}
            </div>
        </div>
    </div>
@endsection

