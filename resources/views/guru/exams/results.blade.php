@extends('layouts.guru')

@section('title', 'Jawaban Ujian')
@section('header', 'Jawaban Ujian')

@section('content')
    <div class="flex flex-col gap-6">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-lg font-semibold text-emerald-900">{{ $exam->title }}</h2>
                <p class="text-sm text-emerald-500">Mata kuliah: {{ $exam->subject?->name ?? '—' }} • Kelas: {{ $exam->classroom ?? '—' }}</p>
            </div>
            <a href="{{ route('guru.exams.index') }}" class="inline-flex items-center gap-2 px-3 py-2 rounded-lg bg-emerald-100 text-emerald-700 hover:bg-emerald-200">
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
                                    @php
                                        $hasMultipleChoice = !empty($exam->questions_json) && !empty($exam->answer_key_json);
                                        $studentAnswers = $res->answers_json ?? [];
                                    @endphp
                                    
                                    @if($hasMultipleChoice && !empty($studentAnswers))
                                        <button type="button"
                                                class="inline-flex items-center justify-center w-8 h-8 rounded bg-emerald-600 text-white hover:bg-emerald-700"
                                                data-questions="{{ json_encode($exam->questions_json) }}"
                                                data-answers="{{ json_encode($studentAnswers) }}"
                                                data-answer-key="{{ json_encode($exam->answer_key_json) }}"
                                                data-student="{{ $res->student?->name ?? 'Mahasiswa' }}"
                                                onclick="openMultipleChoiceModal(this)"
                                                title="Lihat Jawaban">
                                            <i class="fa-solid fa-eye"></i>
                                        </button>
                                    @elseif($res->answer_text)
                                        <button type="button"
                                                class="inline-flex items-center justify-center w-8 h-8 rounded bg-emerald-600 text-white hover:bg-emerald-700"
                                                data-text="{{ $res->answer_text }}"
                                                data-student="{{ $res->student?->name ?? 'Mahasiswa' }}"
                                                onclick="openAnswerModal(this)"
                                                title="Lihat Jawaban">
                                            <i class="fa-solid fa-eye"></i>
                                        </button>
                                    @elseif($res->answer_path)
                                        <a href="{{ route('guru.exams.results.download', [$exam, $res]) }}" 
                                           class="inline-flex items-center justify-center w-8 h-8 rounded bg-emerald-600 text-white hover:bg-emerald-700"
                                           title="Unduh Berkas">
                                            <i class="fa-solid fa-download"></i>
                                        </a>
                                    @else
                                        —
                                    @endif
                                </td>
                                <td class="px-4 py-3">
                                    @if($res->status === 'auto_finished')
                                        <span class="text-red-600">{{ $res->notes ?? 'Ujian selesai otomatis: keluar tab/layar.' }}</span>
                                    @else
                                        <span class="text-emerald-800">{{ $res->notes ?? '—' }}</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-emerald-800">
                                    @if($res->answer_path)
                                        <a href="{{ route('guru.exams.results.download', [$exam, $res]) }}" class="inline-flex items-center gap-2 px-2 py-1 rounded bg-emerald-600 text-white hover:bg-emerald-700">
                                            <i class="fa-solid fa-download"></i>
                                            <span>Unduh Berkas</span>
                                        </a>
                                    @else
                                        —
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-4 py-6 text-center text-emerald-400">Belum ada jawaban mahasiswa.</td>
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
                <div class="w-full max-w-3xl max-h-[90vh] bg-white rounded-xl shadow-xl border border-emerald-100 flex flex-col">
                    <div class="px-4 py-3 border-b border-emerald-100 flex items-center justify-between flex-shrink-0">
                        <h3 id="answerModalTitle" class="text-lg font-semibold text-emerald-900">Jawaban Mahasiswa</h3>
                        <button type="button" class="p-2 rounded bg-emerald-100 text-emerald-700 hover:bg-emerald-200" onclick="closeAnswerModal()">
                            <i class="fa-solid fa-xmark"></i>
                        </button>
                    </div>
                    <div class="p-4 overflow-y-auto flex-1">
                        <div id="answerModalBody" class="whitespace-pre-wrap text-emerald-900"></div>
                    </div>
                    <div class="px-4 py-3 border-t border-emerald-100 flex justify-end flex-shrink-0">
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
            
            function openMultipleChoiceModal(btn) {
                var modal = document.getElementById('answerModal');
                var title = document.getElementById('answerModalTitle');
                var body = document.getElementById('answerModalBody');
                
                try {
                    var questions = JSON.parse(btn.getAttribute('data-questions') || '[]');
                    var answers = JSON.parse(btn.getAttribute('data-answers') || '[]');
                    var answerKey = JSON.parse(btn.getAttribute('data-answer-key') || '[]');
                    var studentName = btn.getAttribute('data-student') || 'Mahasiswa';
                    
                    title.textContent = 'Jawaban Pilihan Ganda: ' + studentName;
                    
                    var html = '<div class="space-y-4">';
                    questions.forEach(function(q, index) {
                        var studentAnswer = answers[index] || null;
                        var correctAnswer = answerKey[index] || null;
                        var isCorrect = studentAnswer === correctAnswer;
                        
                        html += '<div class="border border-emerald-200 rounded-lg p-4 ' + (isCorrect ? 'bg-emerald-50' : 'bg-red-50') + '">';
                        html += '<div class="font-medium text-emerald-900 mb-3">';
                        html += '<span class="inline-block w-6 h-6 rounded-full bg-emerald-100 text-emerald-700 text-xs font-semibold flex items-center justify-center mr-2">' + (index + 1) + '</span>';
                        html += q.question || '';
                        html += '</div>';
                        html += '<div class="space-y-2">';
                        
                        ['A', 'B', 'C', 'D'].forEach(function(opt) {
                            var optText = q.options[opt] || '';
                            var isSelected = studentAnswer === opt;
                            var isCorrectOpt = correctAnswer === opt;
                            
                            html += '<div class="p-2 rounded border-2 ' + 
                                (isCorrectOpt ? 'border-emerald-500 bg-emerald-100' : 'border-emerald-200') + 
                                (isSelected ? ' font-medium' : '') + '">';
                            html += '<span class="text-emerald-700">' + opt + '.</span> ';
                            html += '<span class="text-emerald-900">' + optText + '</span>';
                            if (isSelected) {
                                html += ' <span class="ml-2 text-xs font-medium text-emerald-600">(Dipilih)</span>';
                            }
                            if (isCorrectOpt) {
                                html += ' <span class="ml-2 text-xs font-medium text-emerald-600">✓ Benar</span>';
                            }
                            html += '</div>';
                        });
                        
                        html += '</div>';
                        html += '<div class="mt-3 pt-3 border-t border-emerald-200">';
                        html += '<span class="text-sm text-emerald-700">Jawaban Mahasiswa: <strong>' + (studentAnswer || '—') + '</strong></span>';
                        html += ' | ';
                        html += '<span class="text-sm text-emerald-700">Jawaban Benar: <strong>' + (correctAnswer || '—') + '</strong></span>';
                        if (isCorrect) {
                            html += ' <span class="ml-2 text-emerald-600 font-medium">✓</span>';
                        } else {
                            html += ' <span class="ml-2 text-red-600 font-medium">✗</span>';
                        }
                        html += '</div>';
                        html += '</div>';
                    });
                    html += '</div>';
                    
                    body.innerHTML = html;
                    modal.classList.remove('hidden');
                } catch (e) {
                    console.error('Error parsing data:', e);
                    alert('Terjadi kesalahan saat menampilkan jawaban.');
                }
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