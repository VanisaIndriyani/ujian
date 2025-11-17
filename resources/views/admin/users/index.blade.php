@extends('layouts.admin')

@section('title', 'Kelola Pengguna')
@section('header', 'Data Guru & Mahasiswa')

@section('content')
    <div class="flex flex-col gap-6">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div>
                <h2 class="text-lg font-semibold text-emerald-900">Daftar Pengguna</h2>
                <p class="text-sm text-emerald-500">Kelola akun guru dan mahasiswa secara terpusat.</p>
            </div>
            <a href="{{ route('admin.users.create', ['role' => $role]) }}" title="Tambah Pengguna" aria-label="Tambah Pengguna"
               class="inline-flex items-center justify-center p-2 rounded-lg bg-emerald-600 text-white shadow hover:bg-emerald-700">
                <i class="fa-solid fa-plus"></i>
                <span class="sr-only">Tambah Pengguna</span>
            </a>
        </div>

        <div class="bg-white border border-emerald-100 rounded-2xl shadow">
            <div class="p-4 border-b border-emerald-100 flex flex-wrap items-center gap-3 text-sm">
                <div class="flex items-center gap-3">
                    <span class="text-emerald-500 font-medium">Filter Role:</span>
                    <a href="{{ route('admin.users.index', ['role' => 'guru']) }}" class="px-3 py-1 rounded-lg {{ $role === 'guru' ? 'bg-emerald-500 text-white' : 'bg-emerald-100 text-emerald-600' }}">Guru</a>
                    <a href="{{ route('admin.users.index', ['role' => 'murid']) }}" class="px-3 py-1 rounded-lg {{ $role === 'murid' ? 'bg-emerald-500 text-white' : 'bg-emerald-100 text-emerald-600' }}">Mahasiswa</a>
                </div>

                <form action="{{ route('admin.users.index') }}" method="GET" class="ml-auto flex items-center gap-2">
                    <input type="hidden" name="role" value="{{ $role }}">
                    <div class="relative">
                        <span class="absolute left-3 top-1/2 -translate-y-1/2 text-emerald-400">
                            <i class="fa-solid fa-magnifying-glass"></i>
                        </span>
                        <input type="text" name="q" value="{{ $search ?? '' }}" placeholder="Cari nama, email, NIP/NIM"
                               class="pl-9 w-64 rounded-lg border border-emerald-200 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-transparent">
                    </div>
                    <button type="submit" class="px-3 py-2 rounded-lg bg-emerald-100 text-emerald-600 hover:bg-emerald-200">Cari</button>
                    @if (!empty($search))
                        <a href="{{ route('admin.users.index', ['role' => $role]) }}" class="px-3 py-2 rounded-lg border border-emerald-200 text-emerald-600 hover:bg-emerald-50">Reset</a>
                    @endif
                </form>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-emerald-100 text-sm">
                    <thead class="bg-emerald-50/60 text-emerald-600 uppercase text-xs tracking-wider">
                        <tr>
                            <th class="px-4 py-3 text-left">Foto</th>
                            <th class="px-4 py-3 text-left">Nama</th>
                            <th class="px-4 py-3 text-left">Email</th>
                            <th class="px-4 py-3 text-left">NIP/NIM</th>
                            <th class="px-4 py-3 text-left">Kelas</th>
                            <th class="px-4 py-3 text-left">Terdaftar</th>
                            <th class="px-4 py-3 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-emerald-50">
                        @forelse ($users as $user)
                            <tr class="hover:bg-emerald-50/40">
                                <td class="px-4 py-3">
                                    <img src="{{ $user->profile_photo_url }}" alt="Foto {{ $user->name }}" class="h-9 w-9 rounded-full object-cover ring-1 ring-emerald-200">
                                </td>
                                <td class="px-4 py-3 font-medium text-emerald-900">
                                    {{ $user->name }}
                                    <p class="text-xs text-emerald-400 uppercase tracking-wide mt-0.5">{{ strtoupper($user->role) }}</p>
                                </td>
                                <td class="px-4 py-3 text-emerald-600">{{ $user->email ?? '—' }}</td>
                                <td class="px-4 py-3 text-emerald-600">
                                    {{ $user->role === 'guru' ? ($user->nip ?? '—') : ($user->nisn ?? '—') }}
                                </td>
                                <td class="px-4 py-3 text-emerald-600">{{ $user->classroom ?? '—' }}</td>
                                <td class="px-4 py-3 text-emerald-600">{{ $user->created_at?->format('d M Y') }}</td>
                                <td class="px-4 py-3">
                                    <div class="flex justify-end gap-2">
                                        <a href="{{ route('admin.users.show', $user) }}" title="Detail" aria-label="Detail" class="inline-flex items-center justify-center p-2 rounded-lg bg-emerald-100 text-emerald-600 hover:bg-emerald-200">
                                            <i class="fa-solid fa-eye"></i>
                                        </a>
                                        <form action="{{ route('admin.users.reset_password', $user) }}" method="POST" onsubmit="return confirm('Reset password pengguna ini?')">
                                            @csrf
                                            <button type="submit" title="Reset Password" aria-label="Reset Password" class="inline-flex items-center justify-center p-2 rounded-lg bg-emerald-600 text-white hover:bg-emerald-700">
                                                <i class="fa-solid fa-key"></i>
                                            </button>
                                        </form>
                                        <a href="{{ route('admin.users.edit', $user) }}" title="Ubah" aria-label="Ubah" class="inline-flex items-center justify-center p-2 rounded-lg bg-emerald-100 text-emerald-600 hover:bg-emerald-200">
                                            <i class="fa-solid fa-pen-to-square"></i>
                                        </a>
                                        <form action="{{ route('admin.users.destroy', $user) }}" method="POST" onsubmit="return confirm('Hapus data pengguna ini?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" title="Hapus" aria-label="Hapus" class="inline-flex items-center justify-center p-2 rounded-lg bg-red-100 text-red-600 hover:bg-red-200">
                                                <i class="fa-solid fa-trash-can"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-4 py-6 text-center text-emerald-400">Belum ada data pengguna.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="px-4 py-3 border-t border-emerald-100">
                {{ $users->links() }}
            </div>
        </div>
    </div>
@endsection

