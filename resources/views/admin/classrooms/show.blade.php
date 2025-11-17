@extends('layouts.admin')

@section('title', 'Detail Jurusan')
@section('header', 'Detail Jurusan: ' . $classroom->name)

@section('content')
    <div class="bg-white border border-emerald-100 rounded-2xl shadow p-6 space-y-6">
        <div class="flex items-center justify-between">
            <h2 class="text-emerald-900 font-semibold">Anggota Jurusan</h2>
            <a href="{{ route('admin.classrooms.index') }}" class="inline-flex items-center gap-2 px-4 py-2 rounded-lg border border-emerald-200 text-emerald-700 hover:bg-emerald-50">
                <i class="fa-solid fa-arrow-left"></i>
                <span>Kembali</span>
            </a>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-emerald-100 text-sm">
                <thead class="bg-emerald-50/60 text-emerald-600 uppercase text-xs tracking-wider">
                    <tr>
                        <th class="px-4 py-3 text-left">Nama Mahasiswa</th>
                        <th class="px-4 py-3 text-left">NIM</th>
                        <th class="px-4 py-3 text-left">Email</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-emerald-50">
                @forelse ($students as $student)
                    <tr class="hover:bg-emerald-50/40">
                        <td class="px-4 py-3 text-emerald-900 font-medium">{{ $student->name }}</td>
                        <td class="px-4 py-3 text-emerald-700">{{ $student->nisn ?? '—' }}</td>
                        <td class="px-4 py-3 text-emerald-700">{{ $student->email ?? '—' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="3" class="px-4 py-6 text-emerald-600">Belum ada mahasiswa di kela  s ini.</td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>

        <div>
            {{ $students->links() }}
        </div>
    </div>
@endsection