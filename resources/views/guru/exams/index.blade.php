@extends('layouts.guru')

@section('title', 'Ujian Matakuliah')
@section('header', 'Ujian Matakuliah')

@section('content')
    <div class="flex flex-col gap-6">
        <div class="flex flex-wrap items-center justify-between gap-4">
            <div>
                <h2 class="text-lg font-semibold text-emerald-900">Ujian Matakuliah Terdaftar</h2>
                <p class="text-sm text-emerald-500">Tambah, ubah, lihat jawaban, dan hapus ujian.</p>
            </div>
            <a href="{{ route('guru.exams.create') }}" title="Tambah Ujian Matakuliah" aria-label="Tambah Ujian Matakuliah" class="inline-flex items-center justify-center p-2 rounded-lg bg-emerald-600 text-white shadow hover:bg-emerald-700">
                <i class="fa-solid fa-plus"></i>
                <span class="sr-only">Tambah Ujian Matakuliah</span>
            </a>
        </div>

        <div class="bg-white border border-emerald-100 rounded-2xl shadow overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-emerald-100 text-sm">
                    <thead class="bg-emerald-50/60 text-emerald-600 uppercase text-xs tracking-wider">
                        <tr>
                            <th class="px-4 py-3 text-left">Jurusan</th>
                            <th class="px-4 py-3 text-left">Judul</th>
                            <th class="px-4 py-3 text-left">Mata Kuliah</th>
                            <th class="px-4 py-3 text-left">Semester</th>
                            <th class="px-4 py-3 text-left">Mulai</th>
                            <th class="px-4 py-3 text-left">Selesai</th>
                          
                            <th class="px-4 py-3 text-left">Aksi</th>
                        </tr>
                    </thead>
                <tbody class="divide-y divide-emerald-50">
                        @forelse ($exams as $exam)
                            <tr class="hover:bg-emerald-50/40">
                                <td class="px-4 py-3 text-emerald-600">{{ $exam->classroom ?? '—' }}</td>
                                <td class="px-4 py-3 font-medium text-emerald-900">{{ $exam->title }}</td>
                                <td class="px-4 py-3 text-emerald-600">{{ $exam->subject?->name ?? '—' }}</td>
                                <td class="px-4 py-3 text-emerald-600">{{ $exam->semester ? 'Semester ' . $exam->semester : '—' }}</td>
                                <td class="px-4 py-3 text-emerald-600">{{ optional($exam->start_at)->format('d M Y H:i') ?? '—' }}</td>
                                <td class="px-4 py-3 text-emerald-600">{{ optional($exam->end_at)->format('d M Y H:i') ?? '—' }}</td>

<td class="px-4 py-3 text-emerald-600">
    <div class="flex items-center gap-2">
        <a href="{{ route('guru.exams.results', $exam) }}" title="Lihat Jawaban" aria-label="Lihat Jawaban" class="inline-flex items-center justify-center p-2 rounded-lg bg-emerald-100 text-emerald-600 hover:bg-emerald-200">
            <i class="fa-solid fa-eye"></i>
        </a>
        <a href="{{ route('guru.exams.edit', $exam) }}" title="Ubah" aria-label="Ubah" class="inline-flex items-center justify-center p-2 rounded-lg bg-emerald-100 text-emerald-600 hover:bg-emerald-200">
            <i class="fa-solid fa-pen-to-square"></i>
        </a>
        <form action="{{ route('guru.exams.destroy', $exam) }}" method="POST" onsubmit="return confirm('Hapus ujian ini?')">
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
                                <td colspan="7" class="px-4 py-6 text-center text-emerald-400">Belum ada jadwal ujian matakuliah.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="px-4 py-3 border-t border-emerald-100">
                {{ $exams->links() }}
            </div>
        </div>
    </div>
@endsection

