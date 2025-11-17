@extends('layouts.guru')

@section('title', 'Ubah Ujian Matakuliah')
@section('header', 'Ubah Ujian Matakuliah')

@section('content')
    <div class="bg-white border border-emerald-100 rounded-2xl shadow p-6">
        <form action="{{ route('guru.exams.update', $exam) }}" method="POST" enctype="multipart/form-data" class="space-y-6">
            @csrf
            @method('PUT')
            @include('guru.exams._form', ['exam' => $exam])

            <div class="flex items-center justify-end gap-2">
                <a href="{{ route('guru.exams.index') }}" class="px-4 py-2 rounded-lg border border-emerald-200 text-emerald-600 hover:bg-emerald-50">Batal</a>
                <button type="submit" class="px-4 py-2 rounded-lg bg-emerald-600 text-white font-medium shadow hover:bg-emerald-700">Simpan Perubahan</button>
            </div>
        </form>
    </div>
@endsection