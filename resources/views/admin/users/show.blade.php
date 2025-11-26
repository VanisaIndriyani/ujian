@extends('layouts.admin')

@section('title', 'Detail Pengguna')
@section('header', 'Detail Pengguna')

@section('content')
    <div class="flex flex-col gap-6">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-lg font-semibold text-emerald-900">{{ $user->name }}</h2>
                <p class="text-sm text-emerald-500">Role: {{ ucfirst($user->role) }}</p>
            </div>
            <a href="{{ route('admin.users.index', ['role' => $user->role]) }}" class="inline-flex items-center gap-2 px-4 py-2 rounded-lg border border-emerald-200 text-emerald-600 hover:bg-emerald-50">&larr; Kembali</a>
        </div>

        <div class="gs="flex justify-between"><dt>Tingkat</dt><dd>{{ $user->classroom ?? '—' }}</dd></div>
                    <div class="flex justify-between"><dt>Dibuat</dt><dd>{{ optional($user->created_at)->format('d M Y') }}</dd></div>
                </dl>
            </div>

            <div class="md:col-span-2 bg-white border border-emerald-10rid md:grid-cols-3 gap-5">
            <div class="bg-white border border-emerald-100 rounded-2xl shadow p-6 space-y-3">
                <h3 class="text-emerald-900 font-semibold">Informasi Akun</h3>
                <dl class="text-sm text-emerald-700 space-y-2">
                    <div class="flex justify-between"><dt>Email</dt><dd>{{ $user->email ?? '—' }}</dd></div>
                    <div class="flex justify-between"><dt>{{ $user->role === 'guru' ? 'NIP' : 'NIM' }}</dt><dd>{{ $user->role === 'guru' ? ($user->nip ?? '—') : ($user->nisn ?? '—') }}</dd></div>
                    <div clas0 rounded-2xl shadow p-6">
                @if ($user->role === 'guru')
                    <h3 class="text-emerald-900 font-semibold mb-3">Mata Kuliah Diampu</h3>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-emerald-100 text-sm">
                            <thead class="bg-emerald-50/60 text-emerald-600 uppercase text-xs tracking-wider">
                                <tr>
                                    <th class="px-4 py-3 text-left">Kode</th>
                                    <th class="px-4 py-3 text-left">Nama</th>
                                    <th class="px-4 py-3 text-left">Mahasiswa Terdaftar</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-emerald-50">
                                @forelse ($subjects as $subject)
                                    <tr class="hover:bg-emerald-50/40">
                                        <td class="px-4 py-3 text-emerald-600">{{ $subject->code }}</td>
                                        <td class="px-4 py-3 font-medium text-emerald-900">{{ $subject->name }}</td>
                                        <td class="px-4 py-3 text-emerald-600">{{ $subject->students_count }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="3" class="px-4 py-6 text-emerald-400">Belum ada mata kuliah diampu.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <h3 class="text-emerald-900 font-semibold mt-6 mb-2">Kelas yang Terlibat</h3>
                    @if (($classrooms ?? collect())->count() > 0)
                        <div class="flex flex-wrap gap-2">
                            @foreach ($classrooms as $room)
                                <span class="inline-flex items-center px-3 py-1 rounded-lg bg-emerald-100 text-emerald-700 text-xs">{{ $room }}</span>
                            @endforeach
                        </div>
                    @else
                        <p class="text-sm text-emerald-500">Belum ada kelas terdeteksi untuk mata kuliah diampu.</p>
                    @endif
                @else
                    <h3 class="text-emerald-900 font-semibold mb-3">Mata Kuliah Diikuti</h3>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-emerald-100 text-sm">
                            <thead class="bg-emerald-50/60 text-emerald-600 uppercase text-xs tracking-wider">
                                <tr>
                                    <th class="px-4 py-3 text-left">Kode</th>
                                    <th class="px-4 py-3 text-left">Nama</th>
                                    <th class="px-4 py-3 text-left">Dosen</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-emerald-50">
                                @forelse ($subjects as $subject)
                                    <tr class="hover:bg-emerald-50/40">
                                        <td class="px-4 py-3 text-emerald-600">{{ $subject->code }}</td>
                                        <td class="px-4 py-3 font-medium text-emerald-900">{{ $subject->name }}</td>
                                        <td class="px-4 py-3 text-emerald-600">{{ $subject->guru?->name ?? '—' }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="3" class="px-4 py-6 text-emerald-400">Belum terdaftar pada mata kuliah.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection