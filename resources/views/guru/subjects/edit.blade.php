@extends('layouts.guru')

@section('title', 'Edit Mata Kuliah')
@section('header', 'Edit Mata Kuliah')

@section('content')
<div class="bg-white rounded-2xl shadow border border-emerald-100 overflow-hidden">
<div class="px-6 py-4 border-b border-emerald-100 flex items-center justify-between">
<h2 class="text-lg font-semibold text-emerald-900">Form Mata Kuliah</h2>
<a href="{{ route('guru.subjects.index') }}" class="inline-flex items-center gap-2 px-4 py-2 rounded-lg border border-emerald-200 text-emerald-600 hover:bg-emerald-50">
                &larr; Kembali
            </a>
        </div>
        <form action="{{ route('guru.subjects.update', $subject) }}" method="POST" class="p-6 space-y-4">
            @csrf
            @method('PUT')
            <div>
<label class="block text-sm font-medium text-emerald-700">Kode</label>
<input type="text" name="code" value="{{ old('code', $subject->code) }}" class="mt-1 w-full rounded-lg border-emerald-200 focus:border-emerald-400 focus:ring-emerald-200" required>
                @error('code')<p class="text-sm text-red-600 mt-1">{{ $message }}</p>@enderror
            </div>
            <div>
<label class="block text-sm font-medium text-emerald-700">Nama</label>
<input type="text" name="name" value="{{ old('name', $subject->name) }}" class="mt-1 w-full rounded-lg border-emerald-200 focus:border-emerald-400 focus:ring-emerald-200" required>
                @error('name')<p class="text-sm text-red-600 mt-1">{{ $message }}</p>@enderror
            </div>
            <div>
<label class="block text-sm font-medium text-emerald-700">Deskripsi</label>
<textarea name="description" rows="4" class="mt-1 w-full rounded-lg border-emerald-200 focus:border-emerald-400 focus:ring-emerald-200">{{ old('description', $subject->description) }}</textarea>
                @error('description')<p class="text-sm text-red-600 mt-1">{{ $message }}</p>@enderror
            </div>
            <div class="flex justify-end gap-2">
<a href="{{ route('guru.subjects.index') }}" class="px-4 py-2 rounded-lg border border-emerald-200 text-emerald-600 hover:bg-emerald-50">Batal</a>
<button type="submit" class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-emerald-600 text-white shadow hover:bg-emerald-700">
                    <i class="fa-solid fa-check"></i> Simpan Perubahan
                </button>
            </div>
        </form>
    </div>
@endsection