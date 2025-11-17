@extends('layouts.guru')

@section('title', 'Mata Kuliah Saya')
@section('header', 'Mata Kuliah yang Diampu')

@section('content')
    <div class="flex flex-col gap-6">
        <div class="flex flex-wrap items-center justify-between gap-4">
            <div>
<h2 class="text-lg font-semibold text-emerald-900">Kelola mata kuliah</h2>
<p class="text-sm text-emerald-500">Tambah, ubah, dan hapus mata kuliah yang Anda ampu.</p>
            </div>
            <a href="{{ route('guru.subjects.create') }}" title="Tambah Mata Kuliah" aria-label="Tambah Mata Kuliah"
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
                            <th class="px-4 py-3 text-left">Kode</th>
                            <th class="px-4 py-3 text-left">Nama</th>
                            <th class="px-4 py-3 text-left">Deskripsi</th>
                            <th class="px-4 py-3 text-left">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-emerald-50">
                        @forelse ($subjects as $subject)
                            <tr>
<td class="px-4 py-3 text-emerald-900">{{ $subject->code }}</td>
<td class="px-4 py-3 text-emerald-900">{{ $subject->name }}</td>
<td class="px-4 py-3 text-emerald-700">{{ $subject->description }}</td>
                                <td class="px-4 py-3">
                                    <div class="flex items-center gap-2">
                                        <a href="{{ route('guru.subjects.edit', $subject) }}" title="Edit" aria-label="Edit"
class="inline-flex items-center justify-center p-2 rounded-lg bg-emerald-500 text-white shadow hover:bg-emerald-600">
                                            <i class="fa-solid fa-pen-to-square"></i>
                                            <span class="sr-only">Edit</span>
                                        </a>
                                        <form action="{{ route('guru.subjects.destroy', $subject) }}" method="POST" onsubmit="return confirm('Hapus mata kuliah ini?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" title="Hapus" aria-label="Hapus"
                                                    class="inline-flex items-center justify-center p-2 rounded-lg bg-red-500 text-white shadow hover:bg-red-600">
                                                <i class="fa-solid fa-trash-can"></i>
                                                <span class="sr-only">Hapus</span>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
<td colspan="4" class="px-4 py-6 text-center text-emerald-600">Belum ada mata kuliah.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div>
            {{ $subjects->links() }}
        </div>
    </div>
@endsection