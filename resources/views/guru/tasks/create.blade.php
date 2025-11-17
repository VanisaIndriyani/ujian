@extends('layouts.guru')

@section('title', 'Buat Tugas')
@section('header', 'Buat Tugas Baru')

@section('content')
<div class="bg-white border border-emerald-100 rounded-2xl shadow p-6">
        <form action="{{ route('guru.tasks.store') }}" method="POST" class="space-y-6">
            @csrf
            @include('guru.tasks._form')

            <div class="flex items-center justify-end gap-2">
<a href="{{ route('guru.tasks.index') }}" class="px-4 py-2 rounded-lg border border-emerald-200 text-emerald-600 hover:bg-emerald-50">Batal</a>
<button type="submit" class="px-4 py-2 rounded-lg bg-emerald-600 text-white font-medium shadow hover:bg-emerald-700">Simpan</button>
            </div>
        </form>
    </div>
@endsection

