@extends('layouts.murid')

@section('title', 'Kumpulkan Tugas')
@section('header', 'Kumpulkan Tugas')

@section('content')
<div class="bg-white border border-emerald-100 rounded-2xl shadow p-6">
        <form action="{{ route('murid.tasks.store') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
            @csrf

            <div class="grid md:grid-cols-2 gap-5">
                @if (request('assignment_id'))
                    <input type="hidden" name="assignment_id" value="{{ request('assignment_id') }}">
                @endif

                <div class="md:col-span-2">
<label class="block text-sm font-medium text-emerald-600">Jawaban / Catatan</label>
<textarea name="answer" rows="4" class="mt-1 w-full rounded-xl border border-emerald-200 px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-transparent" placeholder="Tuliskan jawaban atau catatan untuk dosen">{{ old('answer') }}</textarea>
                </div>

                <div class="md:col-span-2">
<label class="block text-sm font-medium text-emerald-600">Upload File (Opsional)</label>
<input type="file" name="file" class="mt-1 w-full text-sm text-emerald-600 file:mr-4 file:rounded-xl file:border-0 file:bg-emerald-600 file:text-white file:px-4 file:py-2 file:font-medium hover:file:bg-emerald-700">
<p class="text-xs text-emerald-400 mt-1">Format PDF/Word/Gambar, maksimal 2MB.</p>
                </div>
            </div>

            <div class="flex items-center justify-end gap-2">
<a href="{{ route('murid.tasks.index') }}" class="px-4 py-2 rounded-lg border border-emerald-200 text-emerald-600">Batal</a>
<button type="submit" class="px-4 py-2 rounded-lg bg-emerald-600 text-white font-medium shadow hover:bg-emerald-700">Kirim Tugas</button>
            </div>
        </form>
    </div>
@endsection

