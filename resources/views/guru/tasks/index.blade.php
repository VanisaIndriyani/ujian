@extends('layouts.guru')

@section('title', 'Kelola Tugas')
@section('header', 'Daftar Tugas')

@section('content')
    <div class="flex flex-col gap-6">
        <div class="flex flex-wrap items-center justify-between gap-4">
            <div>
                <h2 class="text-lg font-semibold text-emerald-900">Tugas yang Anda buat</h2>
                <p class="text-sm text-emerald-500">Kelola materi, deadline, dan detail tugas.</p>
            </div>
            <a href="{{ route('guru.tasks.create') }}" title="Buat Tugas" aria-label="Buat Tugas"
               class="inline-flex items-center justify-center p-2 rounded-lg bg-emerald-600 text-white shadow hover:bg-emerald-700">
                <i class="fa-solid fa-plus"></i>
                <span class="sr-only">Buat Tugas</span>
            </a>
        </div>

        <div class="bg-white border border-emerald-100 rounded-2xl shadow overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-emerald-100 text-sm">
                    <thead class="bg-emerald-50/60 text-emerald-600 uppercase text-xs tracking-wider">
                        <tr>
                            <th class="px-4 py-3 text-left">Judul</th>
                            <th class="px-4 py-3 text-left">Mata Kuliah</th>
                            <th class="px-4 py-3 text-left">Semester</th>
                            <th class="px-4 py-3 text-left">Jurusan</th>
                            <th class="px-4 py-3 text-left">Tenggat</th>
                            <th class="px-4 py-3 text-left">Pengumpulan</th>
                            <th class="px-4 py-3 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-emerald-50">
                        @forelse ($assignments as $task)
                            <tr class="hover:bg-emerald-50/40">
                                <td class="px-4 py-3 font-medium text-emerald-900">{{ $task->title }}</td>
                                <td class="px-4 py-3 text-emerald-600">{{ $task->subject?->name ?? '—' }}</td>
                                <td class="px-4 py-3 text-emerald-600">{{ $task->semester ? 'Semester ' . $task->semester : '—' }}</td>
                                <td class="px-4 py-3 text-emerald-600">{{ $task->classroom ?? 'Semua jurusan' }}</td>
                                <td class="px-4 py-3 text-emerald-600">{{ optional($task->due_at)->format('d M Y H:i') ?? 'Tidak ditentukan' }}</td>
                                <td class="px-4 py-3 text-emerald-600">
                                    <span class="inline-flex items-center px-3 py-1 rounded-full bg-emerald-100 text-emerald-700 text-xs font-medium">
                                        {{ $task->submissions_count }} Pengumpulan
                                    </span>
                                </td>
                                <td class="px-4 py-3">
                                    <div class="flex justify-end gap-2">
                                        <a href="{{ route('guru.tasks.show', $task) }}" title="Detail" aria-label="Detail" class="inline-flex items-center justify-center p-2 rounded-lg bg-emerald-100 text-emerald-600 hover:bg-emerald-200">
                                            <i class="fa-solid fa-eye"></i>
                                        </a>
                                        <a href="{{ route('guru.tasks.edit', $task) }}" title="Ubah" aria-label="Ubah" class="inline-flex items-center justify-center p-2 rounded-lg bg-emerald-100 text-emerald-600 hover:bg-emerald-200">
                                            <i class="fa-solid fa-pen-to-square"></i>
                                        </a>
                                        <form action="{{ route('guru.tasks.destroy', $task) }}" method="POST" onsubmit="return confirm('Hapus tugas ini?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" title="Hapus" aria-label="Hapus" class="inline-flex items-center justify-center p-2 rounded-lg bg-red-100 text-red-600 hover:bg-red-200">
                                                <i class="fa-solid fa-trash-can"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-4 py-6 text-center text-emerald-400">Belum ada tugas yang dibuat.</td>
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

