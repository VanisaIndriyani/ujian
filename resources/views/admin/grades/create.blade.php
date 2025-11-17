@extends('layouts.admin')

@section('title', 'Tambah Nilai')
@section('header', 'Tambah Nilai Semester')

@section('content')
    <div class="bg-white border border-emerald-100 rounded-2xl shadow p-6">
        <form action="{{ route('admin.grades.store') }}" method="POST" class="space-y-6">
            @csrf
            @include('admin.grades._form')

            <div class="flex items-center justify-end gap-2">
                <a href="{{ route('admin.grades.index') }}" class="px-4 py-2 rounded-lg border border-emerald-200 text-emerald-600">Batal</a>
                <button type="submit" class="px-4 py-2 rounded-lg bg-emerald-600 text-white font-medium shadow hover:bg-emerald-700">Simpan</button>
            </div>
        </form>
    </div>
@endsection

