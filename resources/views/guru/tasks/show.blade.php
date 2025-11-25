@extends('layouts.guru')

@php
    use Illuminate\Support\Facades\Storage;
    use Illuminate\Support\Str;
@endphp

@section('title', 'Detail Tugas')
@section('header', 'Detail Tugas')

@section('content')
    <div class="space-y-6">
        <div class="bg-white border border-emerald-100 rounded-2xl shadow p-6">
            <div class="flex flex-wrap justify-between gap-4">
                <div>
                    <p class="text-xs tracking-widest uppercase text-emerald-400">Mata Kuliah</p>
                    <h2 class="text-xl font-semibold text-emerald-900">{{ $task->title }}</h2>
                    <p class="text-sm text-emerald-500 mt-1">{{ $task->subject?->name ?? 'Tidak ditentukan' }}</p>
                </div>
                <div class="text-right">
                    <p class="text-xs tracking-widest uppercase text-emerald-400">Tenggat</p>
                    <p class="text-sm font-medium text-emerald-600">
                        {{ optional($task->due_at)->format('d M Y H:i') ?? 'Tidak ditentukan' }}
                    </p>
                </div>
            </div>
            @if ($task->description)
                <div class="mt-4 text-sm text-emerald-700 leading-relaxed">
                    {!! nl2br(e($task->description)) !!}
                </div>
            @endif
        </div>

        <div class="bg-white border border-emerald-100 rounded-2xl shadow overflow-hidden">
            <div class="px-6 py-4 border-b border-emerald-100 flex items-center justify-between">
                <div>
                    <h3 class="text-lg font-semibold text-emerald-900">Pengumpulan Mahasiswa</h3>
                    <p class="text-sm text-emerald-500">Lihat pengumpulan tugas dari mahasiswa.</p>
                </div>
                <a href="{{ route('guru.tasks.index') }}" class="inline-flex items-center gap-2 px-4 py-2 rounded-lg border border-emerald-200 text-emerald-600 hover:bg-emerald-50">
                    &larr; Kembali ke daftar tugas
                </a>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-emerald-100 text-sm">
                    <thead class="bg-emerald-50/80 text-emerald-600 uppercase text-xs tracking-wider">
                        <tr>
                            <th class="px-4 py-3 text-left">Mahasiswa</th>
                            <th class="px-4 py-3 text-left">Pengumpulan</th>
                            <th class="px-4 py-3 text-left">Jawaban</th>
                            <th class="px-4 py-3 text-left">Berkas</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-emerald-50">
                        @forelse ($submissions as $submission)
                            <tr class="align-top">
                                <td class="px-4 py-4 text-emerald-700 w-48">
                                    <p class="font-medium text-emerald-900">{{ $submission->student?->name ?? 'Tidak diketahui' }}</p>
                                    <p class="text-xs text-emerald-400">{{ $submission->student?->nisn ?? '-' }}</p>
                                    <p class="text-xs text-emerald-400">Jurusan: {{ $submission->student?->classroom ?? '-' }}</p>
                                </td>
                                <td class="px-4 py-4 text-emerald-600 w-40">
                                    <p class="text-sm font-medium">
                                        {{ optional($submission->submitted_at)->format('d M Y H:i') ?? 'Tidak tercatat' }}
                                    </p>
                                    <p class="text-xs text-emerald-400">
                                        {{ optional($submission->submitted_at)->diffForHumans() ?? '' }}
                                    </p>
                                </td>
                                <td class="px-4 py-4 text-emerald-600 w-80">
                                    @if ($submission->answer)
                                        <div class="bg-emerald-50 rounded-xl border border-emerald-100 p-3 text-sm leading-relaxed">
                                            {!! nl2br(e(Str::limit($submission->answer, 500))) !!}
                                        </div>
                                    @else
                                        <span class="text-xs text-emerald-400">Belum ada jawaban tertulis.</span>
                                    @endif
                                </td>
                                <td class="px-4 py-4 text-emerald-600 w-36">
                                    @if ($submission->file_path)
                                        <a href="{{ Storage::disk('public')->url($submission->file_path) }}"
                                           target="_blank"
                                           class="inline-flex items-center gap-2 px-3 py-2 rounded-lg bg-emerald-100 text-emerald-700 hover:bg-emerald-200 text-xs font-medium">
                                            📎 Unduh Berkas
                                        </a>
                                    @else
                                        <span class="text-xs text-emerald-400">Tidak ada berkas.</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-4 py-6 text-center text-emerald-400">
                                    Belum ada mahasiswa yang mengumpulkan tugas ini.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection

