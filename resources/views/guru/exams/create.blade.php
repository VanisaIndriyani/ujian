@extends('layouts.guru')

@section('title', 'Buat Ujian Matakuliah')
@section('header', 'Buat Ujian Matakuliah')

@section('content')
<div class="bg-white border border-emerald-100 rounded-2xl shadow p-6">
        <form action="{{ route('guru.exams.store') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
            @csrf
            @include('guru.exams._form')

            <div class="flex items-center justify-end gap-2">
<a href="{{ route('guru.exams.index') }}" class="px-4 py-2 rounded-lg border border-emerald-200 text-emerald-600">Batal</a>
<button type="submit" class="px-4 py-2 rounded-lg bg-emerald-600 text-white font-medium shadow hover:bg-emerald-700">Simpan</button>
            </div>
        </form>
    </div>
@endsection

