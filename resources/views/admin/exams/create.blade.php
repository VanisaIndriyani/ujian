@extends('layouts.admin')

@section('title', 'Tambah Ujian Matakuliah')
@section('header', 'Tambah Ujian Matakuliah')

@section('content')
    <div class="bg-white border border-emerald-100 rounded-2xl shadow p-6">
        <form action="{{ route('admin.exams.store') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
            @csrf
            @include('admin.exams._form')

            <div class="flex items-center justify-end gap-2">
                <a href="{{ route('admin.exams.index') }}" class="px-4 py-2 rounded-lg border border-emerald-200 text-emerald-600">Batal</a>
                <button type="submit" class="px-4 py-2 rounded-lg bg-emerald-600 text-white font-medium shadow hover:bg-emerald-700">Simpan</button>
            </div>
        </form>
    </div>
@endsection

