@extends('layouts.admin')

@section('title', 'Mata Kuliah')
@section('header', 'Kelola Mata Kuliah')

@section('content')
    <div class="flex flex-col gap-6">
        <div class="flex flex-wrap items-center justify-between gap-4">
            <div>
<h2 class="text-lg font-semibold text-emerald-900">Mata Kuliah</h2>
                <p class="text-sm text-emerald-500">Atur kode, nama, dan dosen pengampu.</p>
            </div>
<a href="{{ route('admin.subjects.create') }}" title="Tambah Mata Kuliah" aria-label="Tambah Mata Kuliah"
               class="inline-flex items-center justify-center p-2 rounded-lg bg-emerald-600 text-white shadow hover:bg-emerald-700">
                <i class="fa-solid fa-plus"></i>
<span class="sr-only">Tambah Mata Kuliah</span>
            </a>
        </div>

        <div class="bg-white border border-emerald-100 rounded-2xl shadow overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-emerald-100 text-sm">
                    <thead class="bg-emerald-50/60 text-emerald-600 uppercase text-xs tracking-wider">
                        <tr>
                            <th class="px-4 py-3 text-left">Kode Mata Kuliah</th>
                            <th class="px-4 py-3 text-left">Nama Mata Kuliah</th>
                            <th class="px-4 py-3 text-left">Semester</th>
                            <th class="px-4 py-3 text-left">Jumlah SKS</th>
                            <th class="px-4 py-3 text-left">Dosen Pengampu</th>
                            <th class="px-4 py-3 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-emerald-50">
                        @forelse ($subjects as $subject)
                            <tr class="hover:bg-emerald-50/40">
                                <td class="px-4 py-3 font-mono text-xs text-emerald-500">{{ $subject->code }}</td>
                                <td class="px-4 py-3 font-medium text-emerald-900">{{ $subject->name }}</td>
                                <td class="px-4 py-3 text-emerald-600">{{ $subject->semester ? 'Semester ' . $subject->semester : '—' }}</td>
                                <td class="px-4 py-3 text-emerald-600">{{ $subject->sks ?? '—' }}</td>
                                <td class="px-4 py-3 text-emerald-600">{{ $subject->guru?->name ?? 'Belum ditentukan' }}</td>
                                <td class="px-4 py-3">
                                    <div class="flex justify-end gap-2">
                                        <a href="{{ route('admin.subjects.edit', $subject) }}" title="Ubah" aria-label="Ubah" class="inline-flex items-center justify-center p-2 rounded-lg bg-emerald-100 text-emerald-600 hover:bg-emerald-200">
                                            <i class="fa-solid fa-pen-to-square"></i>
                                        </a>
<form action="{{ route('admin.subjects.destroy', $subject) }}" method="POST" onsubmit="return confirm('Hapus mata kuliah ini?')">
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
                                <td colspan="6" class="px-4 py-6 text-center text-emerald-400">Belum ada data mata kuliah.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="px-4 py-3 border-t border-emerald-100">
                {{ $subjects->links() }}
            </div>
        </div>
    </div>
@endsection

