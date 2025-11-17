@extends('layouts.admin')

@section('title', 'Manajemen Jurusan')
@section('header', 'Daftar Jurusan')

@section('content')
    <div class="bg-white border border-emerald-100 rounded-2xl shadow p-6 space-y-6">
        <div class="flex items-center justify-between">
            <h2 class="text-emerald-900 font-semibold">Jurusan Terdaftar</h2>
            <a href="{{ route('admin.classrooms.create') }}" class="inline-flex items-center justify-center w-10 h-10 rounded-lg bg-emerald-600 text-white shadow hover:bg-emerald-700" title="Tambah Jurusan">
                <i class="fa-solid fa-plus"></i>
                <span class="sr-only">Tambah Jurusan</span>
            </a>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-emerald-100 text-sm">
                <thead class="bg-emerald-50/60 text-emerald-600 uppercase text-xs tracking-wider">
                    <tr>
                        <th class="px-4 py-3 text-left">Nama Jurusan</th>
                        <th class="px-4 py-3 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-emerald-50">
                @forelse ($classrooms as $classroom)
                    <tr class="hover:bg-emerald-50/40">
                        <td class="px-4 py-3 text-emerald-900 font-medium">{{ $classroom->name }}</td>
                        <td class="px-4 py-3 text-right space-x-2">
                            <a href="{{ route('admin.classrooms.show', $classroom) }}" class="inline-flex items-center justify-center w-9 h-9 rounded-xl bg-sky-100 text-sky-700 hover:bg-sky-200" title="Lihat">
                                <i class="fa-solid fa-eye"></i>
                                <span class="sr-only">Lihat</span>
                            </a>
                            <a href="{{ route('admin.classrooms.edit', $classroom) }}" class="inline-flex items-center justify-center w-9 h-9 rounded-xl bg-emerald-100 text-emerald-700 hover:bg-emerald-200" title="Ubah">
                                <i class="fa-solid fa-pen-to-square"></i>
                                <span class="sr-only">Ubah</span>
                            </a>
                            <form action="{{ route('admin.classrooms.destroy', $classroom) }}" method="POST" class="inline-block" onsubmit="return confirm('Hapus Jurusan ini?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="inline-flex items-center justify-center w-9 h-9 rounded-xl bg-red-100 text-red-700 hover:bg-red-200" title="Hapus">
                                    <i class="fa-solid fa-trash-can"></i>
                                    <span class="sr-only">Hapus</span>
                                </button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="2" class="px-4 py-6 text-emerald-600">Belum ada Jurusan. Tambahkan Jurusan baru.</td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>

        <div>
            {{ $classrooms->links() }}
        </div>
    </div>
@endsection