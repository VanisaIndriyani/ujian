@extends('layouts.admin')

@section('title', 'Tambah Jurusan')
@section('header', 'Tambah Jurusan Baru')

@section('content')
    <div class="bg-white border border-emerald-100 rounded-2xl shadow p-6">
        <form action="{{ route('admin.classrooms.store') }}" method="POST" class="space-y-6">
            @csrf

            <div class="grid md:grid-cols-2 gap-5">
                <div>
                    <label class="block text-sm font-medium text-emerald-600">Nama Jurusan</label>
                    <input type="text" name="name" value="{{ old('name') }}" class="mt-1 w-full rounded-xl border border-emerald-200 px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-transparent" required placeholder="Contoh: Radiologi 2025 A">
                </div>
                <div>
                    <label class="block text-sm font-medium text-emerald-600">Semester</label>
                    <select name="semester" class="mt-1 w-full rounded-xl border border-emerald-200 px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-transparent" required>
                        <option value="">— Pilih Semester —</option>
                        @for ($i = 1; $i <= 8; $i++)
                            <option value="{{ $i }}" {{ (int) old('semester') === $i ? 'selected' : '' }}>Semester {{ $i }}</option>
                        @endfor
                    </select>
                </div>
            </div>

            <div class="flex items-center justify-end gap-2">
                <a href="{{ route('admin.classrooms.index') }}" class="px-4 py-2 rounded-lg border border-emerald-200 text-emerald-600">Batal</a>
                <button type="submit" class="px-4 py-2 rounded-lg bg-emerald-600 text-white font-medium shadow hover:bg-emerald-700">Simpan</button>
            </div>
        </form>
    </div>
@endsection