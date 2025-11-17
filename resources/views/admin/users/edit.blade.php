@extends('layouts.admin')

@section('title', 'Ubah Pengguna')
@section('header', 'Ubah Data ' . strtoupper($user->role))

@section('content')
    <div class="bg-white border border-emerald-100 rounded-2xl shadow p-6">
        <form action="{{ route('admin.users.update', $user) }}" method="POST" enctype="multipart/form-data" class="space-y-6">
            @csrf
            @method('PUT')
            @include('admin.users._form', ['user' => $user])

            <div class="flex items-center justify-end gap-2">
                <a href="{{ route('admin.users.index', ['role' => $user->role]) }}" class="px-4 py-2 rounded-lg border border-emerald-200 text-emerald-600">Batal</a>
                <button type="submit" class="px-4 py-2 rounded-lg bg-emerald-600 text-white font-medium shadow hover:bg-emerald-700">Simpan Perubahan</button>
            </div>
        </form>
    </div>
@endsection

