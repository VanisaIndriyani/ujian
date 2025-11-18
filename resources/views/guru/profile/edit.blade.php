@extends('layouts.guru')

@section('title', 'Edit Profil Dosen')
@section('header', 'Edit Profil')

@section('content')
    <div class="bg-white border border-emerald-100 rounded-2xl shadow p-6 max-w-2xl">
        @if (session('success'))
            <div class="mb-4 rounded-md bg-emerald-50 border border-emerald-200 text-emerald-700 px-4 py-3">
                {{ session('success') }}
            </div>
        @endif

        <form action="{{ route('guru.profile.update') }}" method="POST" class="space-y-6">
            @csrf
            @method('PUT')

            <div>
                <label class="block text-sm font-medium text-emerald-600">Nama Lengkap</label>
                <input type="text" name="name" value="{{ old('name', $guru->name) }}" class="mt-1 w-full rounded-xl border border-emerald-200 px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-transparent" required>
            </div>

            <div>
                <label class="block text-sm font-medium text-emerald-600">Email</label>
                <input type="email" name="email" value="{{ old('email', $guru->email) }}" class="mt-1 w-full rounded-xl border border-emerald-200 px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-transparent" required>
            </div>

            <div>
                <label class="block text-sm font-medium text-emerald-600">Kata Sandi Baru</label>
                <div class="relative">
                    <input type="password" name="password" class="mt-1 w-full rounded-xl border border-emerald-200 px-4 py-2.5 pr-10 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-transparent">
                    <button type="button" class="absolute right-3 top-1/2 -translate-y-1/2 text-emerald-600" data-password-toggle aria-label="Tampilkan/Sembunyikan Password">
                        <i class="fa-solid fa-eye"></i>
                    </button>
                </div>
                <p class="text-xs text-emerald-400 mt-1">Kosongkan jika tidak ingin mengubah password.</p>
            </div>

            <div class="flex items-center justify-end gap-2">
                <a href="{{ route('guru.dashboard') }}" class="px-4 py-2 rounded-lg border border-emerald-200 text-emerald-600">Batal</a>
                <button type="submit" class="px-4 py-2 rounded-lg bg-emerald-600 text-white font-medium shadow hover:bg-emerald-700">Simpan Perubahan</button>
            </div>
        </form>
    </div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', () => {
        document.querySelectorAll('[data-password-toggle]').forEach(btn => {
            const input = btn.closest('div').querySelector('input[name="password"]');
            let visible = false;
            btn.addEventListener('click', () => {
                visible = !visible;
                input.type = visible ? 'text' : 'password';
                btn.innerHTML = visible ? '<i class="fa-solid fa-eye-slash"></i>' : '<i class="fa-solid fa-eye"></i>';
            });
        });
    });
}</script>
@endpush
@endsection