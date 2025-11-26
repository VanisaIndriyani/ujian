@extends('layouts.guru')

@section('title', 'Edit Mata Kuliah')
@section('header', 'Edit Mata Kuliah')

@section('content')
    <div class="bg-white border border-emerald-100 rounded-2xl shadow overflow-hidden">
        <div class="px-6 py-4 border-b border-emerald-100 bg-emerald-50/30">
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-xl font-semibold text-emerald-900">Form Edit Mata Kuliah</h2>
                    <p class="text-sm text-emerald-500 mt-1">Perbarui data mata kuliah</p>
                </div>
                <a href="{{ route('guru.subjects.index') }}" class="inline-flex items-center gap-2 px-4 py-2 rounded-lg border border-emerald-200 text-emerald-600 hover:bg-emerald-50 transition-colors">
                    <i class="fa-solid fa-arrow-left"></i>
                    <span>Kembali</span>
                </a>
            </div>
        </div>
        
        <form action="{{ route('guru.subjects.update', $subject) }}" method="POST" class="p-6">
            @csrf
            @method('PUT')
            
            <div class="grid md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-medium text-emerald-600 mb-2">
                        <i class="fa-solid fa-hashtag mr-2"></i>Kode Mata Kuliah
                    </label>
                    <input type="text" name="code" value="{{ old('code', $subject->code) }}" 
                           class="w-full rounded-xl border border-emerald-200 px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-transparent transition-all" 
                           placeholder="Contoh: RAD101" required>
                    @error('code')
                        <p class="text-sm text-red-600 mt-1 flex items-center gap-1">
                            <i class="fa-solid fa-circle-exclamation"></i>{{ $message }}
                        </p>
                    @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-emerald-600 mb-2">
                        <i class="fa-solid fa-book mr-2"></i>Nama Mata Kuliah
                    </label>
                    <input type="text" name="name" value="{{ old('name', $subject->name) }}" 
                           class="w-full rounded-xl border border-emerald-200 px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-transparent transition-all" 
                           placeholder="Contoh: Dasar Radiologi" required>
                    @error('name')
                        <p class="text-sm text-red-600 mt-1 flex items-center gap-1">
                            <i class="fa-solid fa-circle-exclamation"></i>{{ $message }}
                        </p>
                    @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-emerald-600 mb-2">
                        <i class="fa-solid fa-calendar-alt mr-2"></i>Semester
                    </label>
                    <select name="semester" 
                            class="w-full rounded-xl border border-emerald-200 px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-transparent transition-all" 
                            required>
                        <option value="">— Pilih Semester —</option>
                        @for ($i = 1; $i <= 8; $i++)
                            <option value="{{ $i }}" {{ (int) old('semester', $subject->semester ?? 0) === $i ? 'selected' : '' }}>
                                Semester {{ $i }}
                            </option>
                        @endfor
                    </select>
                    @error('semester')
                        <p class="text-sm text-red-600 mt-1 flex items-center gap-1">
                            <i class="fa-solid fa-circle-exclamation"></i>{{ $message }}
                        </p>
                    @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-emerald-600 mb-2">
                        <i class="fa-solid fa-graduation-cap mr-2"></i>Jumlah SKS
                    </label>
                    <input type="number" name="sks" value="{{ old('sks', $subject->sks) }}" min="1" max="10" 
                           class="w-full rounded-xl border border-emerald-200 px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-transparent transition-all" 
                           placeholder="1-10" required>
                    @error('sks')
                        <p class="text-sm text-red-600 mt-1 flex items-center gap-1">
                            <i class="fa-solid fa-circle-exclamation"></i>{{ $message }}
                        </p>
                    @enderror
                </div>
            </div>

            <div class="mt-6 pt-6 border-t border-emerald-100 flex items-center justify-end gap-3">
                <a href="{{ route('guru.subjects.index') }}" 
                   class="px-5 py-2.5 rounded-xl border border-emerald-200 text-emerald-600 hover:bg-emerald-50 transition-colors font-medium">
                    Batal
                </a>
                <button type="submit" 
                        class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-emerald-600 text-white shadow hover:bg-emerald-700 transition-colors font-medium">
                    <i class="fa-solid fa-check"></i>
                    <span>Simpan Perubahan</span>
                </button>
            </div>
        </form>
    </div>
@endsection