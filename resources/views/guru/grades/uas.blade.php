@extends('layouts.guru')

@section('title', 'Nilai UAS')
@section('header', 'Nilai UAS')

@section('content')
<div class="mb-3">
    <a href="{{ route('guru.grades.index') }}" class="inline-flex items-center gap-2 px-3 py-2 rounded-lg bg-emerald-100 text-emerald-600 hover:bg-emerald-200">
        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7 7-7M3 12h18" />
        </svg>
        <span>Kembali</span>
    </a>
    </div>
<div class="bg-white border border-emerald-100 rounded-2xl shadow overflow-hidden">
    <div class="px-4 py-3 border-b border-emerald-100">
        <form method="GET" action="{{ route('guru.grades.uas') }}" class="flex flex-wrap items-center gap-3 text-sm">
            <label class="text-emerald-500 font-medium">Mata Kuliah</label>
            <select name="subject_id" class="rounded-lg border border-emerald-200 px-3 py-2 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-transparent">
                <option value="">Semua</option>
                @foreach ($subjects as $subject)
                    <option value="{{ $subject->id }}" {{ (int) ($subjectId ?? request('subject_id')) === $subject->id ? 'selected' : '' }}>{{ $subject->name }}</option>
                @endforeach
            </select>
            <label class="text-emerald-500 font-medium">Jurusan</label>
            <select name="classroom" class="rounded-lg border border-emerald-200 px-3 py-2 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-transparent">
                <option value="">Semua</option>
                @foreach ($classrooms as $room)
                    <option value="{{ $room }}" {{ ($classroom ?? request('classroom')) === $room ? 'selected' : '' }}>{{ $room }}</option>
                @endforeach
            </select>
            <button class="px-3 py-2 rounded-lg bg-emerald-100 text-emerald-600 hover:bg-emerald-200">Terapkan</button>
        </form>
    </div>

    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-emerald-100 text-sm">
            <thead class="bg-emerald-50/60 text-emerald-600 uppercase text-xs tracking-wider">
                <tr>
                    <th class="px-4 py-3 text-left">Mahasiswa</th>
                    <th class="px-4 py-3 text-left">Jurusan</th>
                    <th class="px-4 py-3 text-left">Mata Kuliah</th>
                    <th class="px-4 py-3 text-left">Judul UAS</th>
                    <th class="px-4 py-3 text-left">Nilai</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-emerald-50">
                @forelse ($results as $res)
                    <tr class="hover:bg-emerald-50/40">
                        <td class="px-4 py-3 text-emerald-600">{{ $res->student?->name ?? '—' }}</td>
                        <td class="px-4 py-3 text-emerald-600">{{ $res->student?->classroom ?? '—' }}</td>
                        <td class="px-4 py-3 text-emerald-600">{{ $res->exam?->subject?->name ?? '—' }}</td>
                        <td class="px-4 py-3 text-emerald-600">{{ $res->exam?->title ?? '—' }}</td>
                        <td class="px-4 py-3">
                            <form method="POST" action="{{ route('guru.exams.results.update', [$res->exam, $res]) }}" class="flex items-center gap-2">
                                @csrf
                                @method('PATCH')
                                <input type="number" name="score" min="0" max="100" step="0.01" value="{{ old('score', $res->score) }}" class="w-24 rounded-lg border border-emerald-200 px-2 py-1">
                                <input type="text" name="notes" placeholder="Catatan" value="{{ old('notes', $res->notes) }}" class="rounded-lg border border-emerald-200 px-2 py-1">
                                <button class="px-3 py-2 rounded-lg bg-emerald-600 text-white text-xs hover:bg-emerald-700">Simpan</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-4 py-6 text-center text-emerald-400">Belum ada data UAS yang dikerjakan.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="px-4 py-3 border-t border-emerald-100">
        {{ $results->links() }}
    </div>
</div>
@endsection