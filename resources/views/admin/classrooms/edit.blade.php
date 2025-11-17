@extends('layouts.admin')

@section('title', 'Ubah Jurusan')
@section('header', 'Ubah Jurusan')

@section('content')
    <div class="bg-white border border-emerald-100 rounded-2xl shadow p-6">
        <form action="{{ route('admin.classrooms.update', $classroom) }}" method="POST" class="space-y-6">
            @csrf
            @method('PUT')

            <div>
                <label class="block text-sm font-medium text-emerald-600">Nama Jurusan</label>
                <input type="text" name="name" value="{{ old('name', $classroom->name) }}" class="mt-1 w-full rounded-xl border border-emerald-200 px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-transparent" required>
            </div>

            <div class="flex items-center justify-end gap-2">
                <a href="{{ route('admin.classrooms.index') }}" class="px-4 py-2 rounded-lg border border-emerald-200 text-emerald-600">Batal</a>
                <button type="submit" class="px-4 py-2 rounded-lg bg-emerald-600 text-white font-medium shadow hover:bg-emerald-700">Simpan Perubahan</button>
            </div>
        </form>
    </div>
@endsection