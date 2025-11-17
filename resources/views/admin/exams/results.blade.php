@extends('layouts.admin')

@section('title', 'Jawaban Ujian')
@section('header', 'Jawaban Ujian')

@section('content')
    <div class="flex flex-col gap-6">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-lg font-semibold text-emerald-900">{{ $exam->title }}</h2>
                <p class="text-sm text-emerald-500">Mata kuliah: {{ $exam->subject?->name ?? '—' }} • Kelas: {{ $exam->classroom ?? '—' }}</p>
            </div>
            <a href="{{ route('admin.exams.index') }}" class="inline-flex items-center gap-2 px-3 py-2 rounded-lg bg-emerald-100 text-emerald-700 hover:bg-emerald-200">
                <i class="fa-solid fa-arrow-left"></i>
                <span>Kembali</span>
            </a>
        </div>

        <div class="bg-white border border-emerald-100 rounded-2xl shadow overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-emerald-100 text-sm">
                    <thead class="bg-emerald-50/60 text-emerald-600 uppercase text-xs tracking-wider">
                        <tr>
                            <th class="px-4 py-3 text-left">Mahasiswa</th>
                            <th class="px-4 py-3 text-left">Kelas</th>
                            <th class="px-4 py-3 text-left">Status</th>
                            <th class="px-4 py-3 text-left">Waktu</th>
                            <th class="px-4 py-3 text-left">Jawaban</th>
                            <th class="px-4 py-3 text-left">Nilai</th>
                            <th class="px-4 py-3 text-left">Catatan</th>
                            <th class="px-4 py-3 text-left">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-emerald-50">
                        @forelse($results as $res)
                            <tr class="hover:bg-emerald-50/40">
                                <td class="px-4 py-3 text-emerald-800">{{ $res->student?->name ?? '—' }}</td>
                                <td class="px-4 py-3 text-emerald-800">{{ $res->student?->classroom ?? '—' }}</td>
                                <td class="px-4 py-3 text-emerald-800">{{ $res->status ?? '—' }}</td>
                                <td class="px-4 py-3 text-emerald-800">{{ optional($res->submitted_at)->format('d M Y H:i') ?? '—' }}</td>
                                <td class="px-4 py-3 text-emerald-800">
                                    @if($res->answer_text)
                                        <div class="max-h-28 overflow-y-auto p-2 bg-emerald-50 rounded">
                                            <div class="whitespace-pre-wrap">{{ Str::limit($res->answer_text, 200) }}</div>
                                        </div>
                                        <button type="button"
                                                class="mt-2 inline-flex items-center gap-2 px-2 py-1 rounded bg-emerald-600 text-white hover:bg-emerald-700"
                                                data-text="{{ $res->answer_text }}"
                                                data-student="{{ $res->student?->name ?? 'Mahasiswa' }}"
                                                onclick="openAnswerModal(this)">
                                            <i class="fa-solid fa-eye"></i>
                                            <span>Lihat</span>
                                        </button>
                                    @elseif($res->answer_path)
                                        <span class="text-emerald-700">Berkas diunggah</span>
                                    @else
                                        —
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-emerald-800">{{ is_null($res->score) ? '—' : $res->score }}</td>
                                <td class="px-4 py-3">
                                    @if($res->status === 'auto_finished')
                                        <span class="text-red-600">{{ $res->notes ?? 'Ujian selesai otomatis: keluar tab/layar.' }}</span>
                                    @else
                                        <span class="text-emerald-800">{{ $res->notes ?? '—' }}</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-emerald-800 space-y-2">
                                    @if($res->answer_path)
                                        <a href="{{ route('admin.exams.results.download', [$exam, $res]) }}" class="inline-flex items-center gap-2 px-2 py-1 rounded bg-emerald-600 text-white hover:bg-emerald-700">
                                            <i class="fa-solid fa-download"></i>
                                            <span>Unduh Berkas</span>
                                        </a>
                                    @endif
                                    <form action="{{ route('admin.exams.results.update', [$exam, $res]) }}" method="POST" class="flex items-center gap-2">
                                        @csrf
                                        @method('PATCH')
                                        <input type="number" name="score" step="0.01" min="0" max="100"
                                               value="{{ old('score', $res->score) }}"
                                               class="w-24 rounded-lg border border-emerald-200 px-2 py-1 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-transparent"
                                               placeholder="Nilai" required>
                                        <input type="hidden" name="notes" value="{{ old('notes', $res->notes) }}">
                                        <button type="submit" class="inline-flex items-center gap-2 px-2 py-1 rounded bg-emerald-600 text-white hover:bg-emerald-700">
                                            <i class="fa-solid fa-floppy-disk"></i>
                                            <span>Simpan Nilai</span>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="px-4 py-6 text-center text-emerald-400">Belum ada jawaban mahasiswa.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="px-4 py-3 border-t border-emerald-100">
                {{ $results->links() }}
            </div>
        </div>
        
        <div id="answerModal" class="fixed inset-0 hidden bg-black/40 z-50">
            <div class="absolute inset-0 flex items-center justify-center p-4">
                <div class="w-full max-w-3xl bg-white rounded-xl shadow-xl border border-emerald-100">
                    <div class="px-4 py-3 border-b border-emerald-100 flex items-center justify-between">
                        <h3 id="answerModalTitle" class="text-lg font-semibold text-emerald-900">Jawaban Mahasiswa</h3>
                        <button type="button" class="p-2 rounded bg-emerald-100 text-emerald-700 hover:bg-emerald-200" onclick="closeAnswerModal()">
                            <i class="fa-solid fa-xmark"></i>
                        </button>
                    </div>
                    <div class="p-4">
                        <div id="answerModalBody" class="whitespace-pre-wrap text-emerald-900"></div>
                    </div>
                    <div class="px-4 py-3 border-t border-emerald-100 flex justify-end">
                        <button type="button" class="px-3 py-2 rounded-lg bg-emerald-600 text-white hover:bg-emerald-700" onclick="closeAnswerModal()">Tutup</button>
                    </div>
                </div>
            </div>
        </div>
        
        <script>
            function openAnswerModal(btn) {
                var modal = document.getElementById('answerModal');
                var title = document.getElementById('answerModalTitle');
                var body = document.getElementById('answerModalBody');
                title.textContent = 'Jawaban: ' + (btn.getAttribute('data-student') || 'Mahasiswa');
                body.textContent = btn.getAttribute('data-text') || '';
                modal.classList.remove('hidden');
            }
            function closeAnswerModal() {
                var modal = document.getElementById('answerModal');
                modal.classList.add('hidden');
            }
            document.addEventListener('keydown', function(e){
                if(e.key === 'Escape') closeAnswerModal();
            });
        </script>
    </div>
@endsection