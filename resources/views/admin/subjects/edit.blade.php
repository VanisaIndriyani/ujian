@extends('layouts.admin')

@section('title', 'Ubah Mata Kuliah')
@section('header', 'Ubah Mata Kuliah')

@section('content')
    <div class="bg-white border border-emerald-100 rounded-2xl shadow p-6">
        <form action="{{ route('admin.subjects.update', $subject) }}" method="POST" class="space-y-6">
            @csrf
            @method('PUT')
            @include('admin.subjects._form', ['subject' => $subject])

            <div class="flex items-center justify-end gap-2">
                <a href="{{ route('admin.subjects.index') }}" class="px-4 py-2 rounded-lg border border-emerald-200 text-emerald-600">Batal</a>
                <button type="submit" class="px-4 py-2 rounded-lg bg-emerald-600 text-white font-medium shadow hover:bg-emerald-700">Simpan Perubahan</button>
            </div>
        </form>
    </div>
@endsection

