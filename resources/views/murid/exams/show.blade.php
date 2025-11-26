@extends('layouts.murid')

@section('title', 'Detail Ujian Matakuliah')
@section('header', 'Detail Ujian Matakuliah')

@section('content')
    <div class="grid lg:grid-cols-3 gap-6">
<div class="lg:col-span-2 bg-white border border-emerald-100 rounded-2xl shadow p-6 space-y-4">
            @php
                $examLocked = isset($result) && in_array($result->status ?? null, ['submitted','auto_finished','finished']);
            @endphp
            <div>
<p class="text-xs uppercase tracking-wide text-emerald-400">Mata Kuliah</p>
<p class="text-lg font-semibold text-emerald-900">{{ $exam->subject?->name ?? '-' }}</p>
            </div>
            <div>
<p class="text-xs uppercase tracking-wide text-emerald-400">Ujian Matakuliah</p>
<p class="text-lg font-semibold text-emerald-900">{{ $exam->title }}</p>
            </div>
            <div>
                <p class="text-xs uppercase tracking-wide text-emerald-400">Jenis Ujian</p>
                <p class="text-lg font-semibold text-emerald-900">{{ $exam->type ?? '—' }}</p>
            </div>
            <div class="grid sm:grid-cols-2 gap-4 text-sm text-emerald-600">
                <div>
<p class="font-medium text-emerald-500 uppercase text-xs">Mulai</p>
                    <p>{{ optional($exam->start_at)->format('d M Y H:i') ?? '-' }}</p>
                </div>
            <div>
<p class="font-medium text-emerald-500 uppercase text-xs">Selesai</p>
                    <p>{{ optional($exam->end_at)->format('d M Y H:i') ?? '-' }}</p>
                </div>
            </div>
            @if($exam->end_at)
            <div id="countdown-card" class="rounded-xl bg-emerald-50 border border-emerald-100 p-4">
                <div class="flex items-center justify-between">
                    <p class="text-sm font-medium text-emerald-800">Waktu tersisa</p>
                    <p id="countdown-text" class="text-lg font-semibold text-emerald-900">—</p>
                </div>
                <div class="mt-3 w-full h-2 bg-emerald-100 rounded overflow-hidden">
                    <div id="countdown-progress" class="h-2 bg-emerald-600 w-0"></div>
                </div>
            </div>
            @endif

            <div id="locked-banner" class="{{ $examLocked ? '' : 'hidden' }} rounded-xl bg-emerald-50 border border-emerald-100 p-4">
                <p class="text-sm text-emerald-700">
                    @if($examLocked)
                        @if(($result->status ?? null) === 'auto_finished')
                            Ujian telah berakhir otomatis karena Anda keluar dari layar/tab. Anda tidak dapat memulai ujian kembali.
                        @else
                            Ujian telah berakhir. Anda tidak dapat memulai ujian kembali.
                        @endif
                    @endif
                </p>
            </div>
            <div class="border-t border-emerald-100 pt-4 text-sm text-emerald-600 leading-relaxed">
{!! nl2br(e($exam->description ?? 'Tidak ada deskripsi ujian matakuliah.')) !!}
            </div>

            @php
                $questions = $exam->questions_json ?? [];
                $answerKey = $exam->answer_key_json ?? [];
                $hasMultipleChoice = !empty($questions) && !empty($answerKey);
                $examLocked = isset($result) && in_array($result->status ?? null, ['submitted','auto_finished','finished']);
                $studentAnswers = $result->answers_json ?? [];
            @endphp

            @if ($hasMultipleChoice)
                <div class="border-t border-emerald-100 pt-4 space-y-4">
                    <div class="flex items-center justify-between">
                        <p class="text-xs uppercase tracking-wide text-emerald-400">Soal Pilihan Ganda</p>
                        @if($examLocked && $result->score !== null)
                            <p class="text-sm font-semibold text-emerald-700">Nilai: {{ number_format($result->score, 2) }}</p>
                        @endif
                    </div>

                    @if(!$examLocked)
                        <div id="mc-exam-intro" class="rounded-xl bg-emerald-50 border border-emerald-100 p-4 space-y-3">
                            <p class="text-sm text-emerald-700">Mode Ujian Pilihan Ganda</p>
                            <p class="text-xs text-emerald-600">Sistem akan meminta layar penuh dan memantau aktivitas Anda selama ujian. Jangan keluar dari halaman ini sampai selesai.</p>
                            <button type="button" id="btn-start-mc-exam" class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-emerald-600 text-white text-sm shadow hover:bg-emerald-700">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-4 h-4"><path d="M5 3l14 9-14 9V3z"/></svg>
                                Mulai Ujian
                            </button>
                        </div>
                    @endif
                    
                    <form id="multiple-choice-form" method="POST" action="{{ route('murid.exams.finish', $exam) }}" class="space-y-4 {{ !$examLocked ? 'hidden' : '' }}" id="mc-exam-wrapper">
                        @csrf
                        @foreach($questions as $index => $question)
                            <div class="border border-emerald-200 rounded-lg p-4 bg-white">
                                <div class="mb-3">
                                    <p class="text-sm font-medium text-emerald-900 mb-2">
                                        <span class="inline-block w-6 h-6 rounded-full bg-emerald-100 text-emerald-700 text-xs font-semibold flex items-center justify-center mr-2">{{ $index + 1 }}</span>
                                        {{ $question['question'] ?? '' }}
                                    </p>
                                </div>
                                <div class="space-y-2">
                                    @foreach(['A', 'B', 'C', 'D'] as $option)
                                        @php
                                            $optionText = $question['options'][$option] ?? '';
                                            $isChecked = isset($studentAnswers[$index]) && $studentAnswers[$index] === $option;
                                            $isCorrect = ($answerKey[$index] ?? '') === $option;
                                            $isWrong = $isChecked && !$isCorrect;
                                        @endphp
                                        <label class="flex items-start gap-3 p-3 rounded-lg border-2 cursor-pointer transition-colors
                                            @if($examLocked)
                                                @if($isCorrect) bg-emerald-50 border-emerald-500
                                                @elseif($isWrong) bg-red-50 border-red-300
                                                @else border-emerald-200
                                                @endif
                                            @else
                                                hover:bg-emerald-50 border-emerald-200
                                            @endif
                                            {{ $isChecked ? 'bg-emerald-50' : '' }}">
                                            <input type="radio" 
                                                   name="answers[{{ $index }}]" 
                                                   value="{{ $option }}"
                                                   {{ $isChecked ? 'checked' : '' }}
                                                   {{ $examLocked ? 'disabled' : '' }}
                                                   class="mt-1">
                                            <div class="flex-1">
                                                <span class="font-medium text-emerald-700">{{ $option }}.</span>
                                                <span class="text-sm text-emerald-900 ml-2">{{ $optionText }}</span>
                                                @if($examLocked && $isCorrect)
                                                    <span class="ml-2 text-xs text-emerald-600 font-medium">✓ Benar</span>
                                                @elseif($examLocked && $isWrong)
                                                    <span class="ml-2 text-xs text-red-600 font-medium">✗ Salah</span>
                                                @endif
                                            </div>
                                        </label>
                                    @endforeach
                                </div>
                            </div>
                        @endforeach
                        
                        @if(!$examLocked)
                            <div class="flex items-center justify-end gap-2 pt-4 border-t border-emerald-100">
                                <button type="button" id="btn-exit-mc-exam" class="px-4 py-2 rounded-lg border border-emerald-200 text-emerald-600 hover:bg-emerald-50">Batal</button>
                                <button type="submit" id="btn-finish-mc-exam" class="px-4 py-2 rounded-lg bg-emerald-600 text-white font-medium shadow hover:bg-emerald-700">Selesai & Lihat Nilai</button>
                            </div>
                        @endif
                    </form>
                </div>
            @endif

            @if ($exam->material_path)
                <div class="border-t border-emerald-100 pt-4 space-y-3">
                    <p class="text-xs uppercase tracking-wide text-emerald-400">Materi/Soal Ujian</p>
                    @php
                        $ext = strtolower(pathinfo($exam->material_path, PATHINFO_EXTENSION));
                        // Normalisasi path agar mendukung nilai seperti "exams/materials/...", "public/exams/...", atau "storage/exams/..."
                        $raw = ltrim($exam->material_path, '/');
                        $normalized = preg_replace('#^(storage/|public/)#', '', $raw);
                        // Cek apakah file exists di storage
                        $fileExists = \Illuminate\Support\Facades\Storage::disk('public')->exists($normalized);
                        // Gunakan asset() helper yang otomatis mengikuti base URL
                        $url = $fileExists ? asset('storage/' . ltrim($normalized, '/')) : null;
                    @endphp
                    @if(!$url || !$fileExists)
                        <div class="rounded-xl border border-red-100 p-4 bg-red-50">
                            <p class="text-sm text-red-700">File materi ujian tidak ditemukan.</p>
                        </div>
                    @elseif ($ext === 'pdf')
                        <iframe src="{{ $url }}#toolbar=0" class="w-full h-[78vh] bg-white" referrerpolicy="no-referrer"></iframe>
                    @else
                        <div class="rounded-xl border border-emerald-100 p-4 bg-emerald-50">
                            <p class="text-sm text-emerald-700">Berkas Word terlampir.</p>
                            <p class="text-xs text-emerald-500 mt-2">Mode editor web akan menampilkan isi DOCX di halaman ini, meminta layar penuh, dan otomatis mengakhiri ujian jika Anda berpindah aplikasi/tab.</p>

                            <div class="mt-3">
                                <button id="btn-start-docx" type="button" class="inline-flex items-center gap-2 px-3 py-2 rounded-lg bg-emerald-600 text-white text-xs hover:bg-emerald-700">Mulai Ujian (Dokumen)</button>
                            </div>

                            <div id="docx-wrapper" class="hidden mt-4 border border-emerald-100 rounded-xl overflow-hidden">
                                <div class="flex items-center justify-between px-3 py-2 bg-emerald-50 border-b border-emerald-100 text-xs text-emerald-600">
                                    <span>Mode Ujian Dokumen Aktif • Tetap di halaman ini sampai selesai.</span>
                                    <button id="btn-exit-docx" type="button" class="px-2 py-1 rounded-md bg-emerald-600 text-white">Selesai</button>
                                </div>
                                <div class="grid lg:grid-cols-2 gap-0">
                                    <div class="p-4 bg-white max-h-[70vh] overflow-auto">
                                        <div id="docx-viewer" class="prose max-w-none"></div>
                                    </div>
                                    <div class="p-4 bg-white border-l border-emerald-100">
                                        <form id="text-answer-form" method="POST" action="{{ route('murid.exams.submit_text', $exam) }}" class="space-y-2">
                                            @csrf
                                            <textarea name="answer_text" class="w-full h-[64vh] border border-emerald-200 rounded-lg p-3 text-sm" placeholder="Ketik jawaban Anda di sini" required>{{ old('answer_text', $result->answer_text ?? '') }}</textarea>
                                            <button type="submit" class="inline-flex items-center gap-2 px-3 py-2 rounded-lg bg-emerald-600 text-white text-xs hover:bg-emerald-700">Simpan Jawaban</button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            @endif

            @if ($exam->question_url && !$exam->material_path)
                <div class="border-t border-emerald-100 pt-4 space-y-3" id="exam-intro">
                    <p class="text-xs uppercase tracking-wide text-emerald-400">Mode Ujian</p>
                    <p class="text-sm text-emerald-600">Ujian akan dibuka di halaman ini dalam mode layar penuh. Sistem akan memantau perpindahan tab, menonaktifkan klik kanan serta beberapa pintasan keyboard.</p>
                    <button id="btn-start-exam" type="button" class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-emerald-600 text-white text-sm shadow hover:bg-emerald-700">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-4 h-4"><path d="M5 3l14 9-14 9V3z"/></svg>
                        Mulai Ujian (CAT)
                    </button>
                </div>

                <div id="exam-frame-wrapper" class="hidden border border-emerald-100 rounded-xl overflow-hidden">
                    <div class="flex items-center justify-between px-3 py-2 bg-emerald-50 border-b border-emerald-100 text-xs text-emerald-600">
                        <span>Mode Ujian Aktif • Tetap di halaman ini sampai selesai.</span>
                        <button id="btn-exit-exam" type="button" class="px-2 py-1 rounded-md bg-emerald-600 text-white">Selesai</button>
                    </div>
                    <iframe id="exam-frame" src="" class="w-full h-[78vh] bg-white" allow="fullscreen" referrerpolicy="no-referrer"></iframe>
                </div>

                <!-- Panel jawaban CAT (opsional) -->
                <div id="cat-answer-panel" class="hidden mt-3 border border-emerald-100 rounded-xl p-4 bg-emerald-50">
                    <div class="flex items-center justify-between">
                        <p class="text-sm text-emerald-700">Catatan/Jawaban (opsional). Teks di sini dapat diunduh sebagai DOCX.</p>
                        <button id="btn-export-cat-docx" type="button" class="px-3 py-2 rounded-lg bg-emerald-600 text-white text-xs hover:bg-emerald-700">Unduh Jawaban (DOCX)</button>
                    </div>
                    <textarea id="cat-answer-text" class="mt-2 w-full h-40 border border-emerald-200 rounded-lg p-3 text-sm" placeholder="Ketik jawaban atau catatan Anda di sini"></textarea>
                    <p class="mt-1 text-xs text-emerald-500">Catatan: konten ini disimpan hanya saat Anda menekan tombol unduh atau simpan jawaban.</p>
                </div>
            @endif
        </div>

        <div class="space-y-6">
            @if ($result && $result->submitted_at)
                <div class="bg-white border border-emerald-100 rounded-2xl shadow p-6 space-y-3">
                    <h3 class="text-lg font-semibold text-emerald-900">Status Ujian</h3>
                    <div class="space-y-2">
                        <div>
                            <p class="text-xs uppercase tracking-wide text-emerald-400">Status</p>
                            <p class="text-sm font-medium text-emerald-700">
                                @if($result->status === 'submitted')
                                    Sudah Dikumpulkan
                                @elseif($result->status === 'auto_finished')
                                    Selesai Otomatis
                                @elseif($result->status === 'finished')
                                    Selesai
                                @else
                                    Dalam Proses
                                @endif
                            </p>
                        </div>
                        <div>
                            <p class="text-xs uppercase tracking-wide text-emerald-400">Waktu Pengumpulan</p>
                            <p class="text-sm text-emerald-600">
                                {{ optional($result->submitted_at)->format('d M Y H:i') ?? '-' }}
                            </p>
                        </div>
                        @if($result->score !== null)
                            <div>
                                <p class="text-xs uppercase tracking-wide text-emerald-400">Nilai</p>
                                <p class="text-2xl font-bold text-emerald-700">{{ number_format($result->score, 2) }}</p>
                            </div>
                        @else
                            <div>
                                <p class="text-xs uppercase tracking-wide text-emerald-400">Nilai</p>
                                <p class="text-sm text-emerald-500">Menunggu penilaian</p>
                            </div>
                        @endif
                        @if($result->notes)
                            <div>
                                <p class="text-xs uppercase tracking-wide text-emerald-400">Catatan</p>
                                <p class="text-sm text-emerald-600">{{ $result->notes }}</p>
                            </div>
                        @endif
                    </div>
                </div>
            @endif

            <div class="bg-white border border-emerald-100 rounded-2xl shadow p-6 space-y-3">
                <p class="text-sm text-emerald-600">Catatan: Untuk keamanan ujian, sistem akan:
                </p>
                <ul class="list-disc pl-5 text-sm text-emerald-600">
                    <li>Meminta layar penuh saat ujian dibuka.</li>
                    <li>Mendeteksi jika jendela/tab kehilangan fokus dan menampilkan peringatan.</li>
                    <li>Mencatat pelanggaran (keluar dari fullscreen, berpindah tab) ke konsol.</li>
                </ul>
                @if ($exam->question_url)
                    <p class="text-sm text-emerald-600">Link ujian CAT:
                        <a href="{{ $exam->question_url }}" target="_blank" rel="noopener" class="text-emerald-700 underline">Buka soal</a>
                    </p>
                @endif
            </div>
        </div>

        <script src="https://unpkg.com/mammoth/mammoth.browser.min.js"></script>
        <script>
            // Mode CAT sederhana: fullscreen + deteksi fokus/tab + blok klik kanan & hotkeys
            (function() {
                const examUrl = "{{ $exam->question_url }}";
                const intro = document.getElementById('exam-intro');
                const frameWrapper = document.getElementById('exam-frame-wrapper');
                const frameEl = document.getElementById('exam-frame');
                const btnStart = document.getElementById('btn-start-exam');
                const btnExit = document.getElementById('btn-exit-exam');
                const btnStartDocx = document.getElementById('btn-start-docx');
                const btnExitDocx = document.getElementById('btn-exit-docx');
                const docxWrapper = document.getElementById('docx-wrapper');
                const docxViewer = document.getElementById('docx-viewer');
                const docxUrl = "{{ isset($url) ? $url : '' }}";
                const csrf = '{{ csrf_token() }}';
                const autoFinishUrl = '{{ route('murid.exams.auto_finish', $exam) }}';
                const finishUrl = '{{ route('murid.exams.finish', $exam) }}';
                const submitTextUrl = '{{ route('murid.exams.submit_text', $exam) }}';
                const heartbeatUrl = '{{ route('murid.exams.heartbeat', $exam) }}';
                const locked = {{ $examLocked ? 'true' : 'false' }};
                @php
                    $initialRemainingMs = $exam->end_at ? max(0, now()->diffInMilliseconds($exam->end_at, false)) : 0;
                    $totalDurationMs = ($exam->start_at && $exam->end_at)
                        ? max(1, \Illuminate\Support\Carbon::parse($exam->start_at)->diffInMilliseconds($exam->end_at, false))
                        : 0;
                @endphp
                const initialRemainingMsServer = {{ $initialRemainingMs }}; // dihitung di server untuk hindari beda zona waktu
                const totalDurationMsServer = {{ $totalDurationMs }};
                const countdownText = document.getElementById('countdown-text');
                const countdownProgress = document.getElementById('countdown-progress');
                const catAnswerPanel = document.getElementById('cat-answer-panel');
                const catAnswerText = document.getElementById('cat-answer-text');
                const btnExportCatDocx = document.getElementById('btn-export-cat-docx');

                const requireFullscreen = () => {
                    const el = document.documentElement;
                    if (!document.fullscreenElement && el.requestFullscreen) {
                        el.requestFullscreen().catch(() => {});
                    }
                };

                const warn = (message) => {
                    // Tampilkan toast/peringatan sederhana
                    const div = document.createElement('div');
                    div.textContent = message;
                    div.style.position = 'fixed';
                    div.style.bottom = '20px';
                    div.style.right = '20px';
                    div.style.padding = '10px 14px';
                    div.style.background = '#fef3c7';
                    div.style.color = '#92400e';
                    div.style.border = '1px solid #fde68a';
                    div.style.borderRadius = '10px';
                    div.style.boxShadow = '0 2px 10px rgba(0,0,0,0.08)';
                    div.style.zIndex = '9999';
                    document.body.appendChild(div);
                    setTimeout(() => div.remove(), 3000);
                };

                let violations = 0;
                let examActive = false;
                let finishedOnce = false;
                let countdownTimer = null;
                let initialRemainingMs = 0;
                let lastRemainingMs = 0;
                let preflightMode = null;
                const VIOLATION_THRESHOLD = 2; // jumlah pelanggaran yang diperbolehkan sebelum auto-submit
                const HEARTBEAT_INTERVAL_MS = 7000; // heartbeat tiap 7 detik
                let heartbeatTimer = null;

                const startExam = () => {
                    examActive = true;
                    if (intro) intro.classList.add('hidden');
                    if (frameWrapper) frameWrapper.classList.remove('hidden');
                    if (frameEl) frameEl.src = examUrl;
                    requireFullscreen();
                    warn('Mode ujian aktif. Tetap di halaman ini.');
                    startCountdown();
                    startHeartbeat();
                    if (catAnswerPanel) catAnswerPanel.classList.remove('hidden');
                };

                const exitExam = () => {
                    examActive = false;
                    if (frameWrapper) frameWrapper.classList.add('hidden');
                    if (intro) intro.classList.remove('hidden');
                    if (docxWrapper) docxWrapper.classList.add('hidden');
                    if (catAnswerPanel) catAnswerPanel.classList.add('hidden');
                    if (document.fullscreenElement && document.exitFullscreen) {
                        document.exitFullscreen().catch(() => {});
                    }
                    warn('Mode ujian dinonaktifkan.');
                    stopCountdown();
                    stopHeartbeat();
                };

                const startDocxExam = async () => {
                    examActive = true;
                    if (intro) intro.classList.add('hidden');
                    if (docxWrapper) docxWrapper.classList.remove('hidden');
                    requireFullscreen();
                    warn('Mode ujian (Dokumen) aktif. Tetap di halaman ini.');
                    startCountdown();
                    startHeartbeat();

                    try {
                        if (docxUrl && docxViewer) {
                            const resp = await fetch(docxUrl);
                            const buf = await resp.arrayBuffer();
                            const result = await mammoth.convertToHtml({arrayBuffer: buf});
                            docxViewer.innerHTML = result.value;
                        }
                    } catch (e) {
                        console.warn('Gagal memuat DOCX ke viewer', e);
                    }
                };

                const autoFinish = async () => {
                    if (finishedOnce) return;
                    finishedOnce = true;
                    try {
                        await fetch(autoFinishUrl, {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': csrf,
                                'Accept': 'application/json',
                            },
                        });
                    } catch (e) {}
                    exitExam();
                    warn('Ujian otomatis selesai karena berpindah layar/tab. Nilai dikosongkan.');
                    const banner = document.getElementById('locked-banner');
                    if (banner) banner.classList.remove('hidden');
                    if (btnStart) { btnStart.disabled = true; btnStart.classList.add('opacity-50','cursor-not-allowed'); }
                    if (btnStartDocx) { btnStartDocx.disabled = true; btnStartDocx.classList.add('opacity-50','cursor-not-allowed'); }
                };

                const finishExam = async () => {
                    if (finishedOnce) return;
                    finishedOnce = true;
                    
                    // Simpan jawaban terakhir jika ada
                    const form = document.getElementById('text-answer-form');
                    const textarea = form ? form.querySelector('textarea[name="answer_text"]') : null;
                    const answer = textarea ? textarea.value.trim() : '';
                    
                    try {
                        const formData = new FormData();
                        if (answer.length >= 5) {
                            formData.append('answer_text', answer);
                        }
                        
                        const response = await fetch(finishUrl, {
                            method: 'POST',
                            headers: { 'X-CSRF-TOKEN': csrf },
                            body: formData,
                        });
                        
                        if (response.ok) {
                            exitExam();
                            warn('Ujian berhasil diselesaikan.');
                            const banner = document.getElementById('locked-banner');
                            if (banner) banner.classList.remove('hidden');
                            if (btnStart) { btnStart.disabled = true; btnStart.classList.add('opacity-50','cursor-not-allowed'); }
                            if (btnStartDocx) { btnStartDocx.disabled = true; btnStartDocx.classList.add('opacity-50','cursor-not-allowed'); }
                            // Reload halaman untuk menampilkan status terkunci
                            setTimeout(() => window.location.reload(), 1000);
                        }
                    } catch (e) {
                        console.error('Error finishing exam:', e);
                        finishedOnce = false;
                    }
                };

                const autoSubmit = async () => {
                    if (finishedOnce) return;
                    const form = document.getElementById('text-answer-form');
                    const textarea = form ? form.querySelector('textarea[name="answer_text"]') : null;
                    const answer = textarea ? textarea.value.trim() : '';
                    if (form && answer.length >= 5) {
                        finishedOnce = true;
                        try {
                            await fetch(submitTextUrl, {
                                method: 'POST',
                                headers: { 'X-CSRF-TOKEN': csrf },
                                body: (() => { const fd = new FormData(); fd.append('answer_text', answer); return fd; })(),
                            });
                            exitExam();
                            warn('Jawaban otomatis disimpan. Ujian selesai.');
                            const banner = document.getElementById('locked-banner');
                            if (banner) banner.classList.remove('hidden');
                            if (btnStart) { btnStart.disabled = true; btnStart.classList.add('opacity-50','cursor-not-allowed'); }
                            if (btnStartDocx) { btnStartDocx.disabled = true; btnStartDocx.classList.add('opacity-50','cursor-not-allowed'); }
                            return;
                        } catch (e) {
                            // fallback ke autoFinish bila gagal
                        }
                    }
                    await autoFinish();
                };

                function stopCountdown() {
                    if (countdownTimer) {
                        clearInterval(countdownTimer);
                        countdownTimer = null;
                    }
                }

                function startCountdown() {
                    if (initialRemainingMsServer <= 0 || countdownTimer) {
                        if (countdownText) countdownText.textContent = '00:00:00';
                        if (countdownProgress) countdownProgress.style.width = '100%';
                        return;
                    }
                    initialRemainingMs = initialRemainingMsServer;
                    const startedAtClientMs = Date.now();

                    function fmt(ms) {
                        const total = Math.max(0, Math.floor(ms / 1000));
                        const h = String(Math.floor(total / 3600)).padStart(2, '0');
                        const m = String(Math.floor((total % 3600) / 60)).padStart(2, '0');
                        const s = String(total % 60).padStart(2, '0');
                        return `${h}:${m}:${s}`;
                    }

                    countdownTimer = setInterval(() => {
                        const elapsedMs = Date.now() - startedAtClientMs;
                        const remainingMs = Math.max(0, initialRemainingMs - elapsedMs);
                        lastRemainingMs = remainingMs;
                        if (countdownText) countdownText.textContent = fmt(remainingMs);
                        if (countdownProgress && initialRemainingMs > 0) {
                            let used = 0;
                            if (totalDurationMsServer > 0) {
                                used = Math.min(100, ((totalDurationMsServer - remainingMs) / totalDurationMsServer) * 100);
                            } else {
                                used = Math.min(100, (elapsedMs / initialRemainingMs) * 100);
                            }
                            countdownProgress.style.width = `${used}%`;
                        }
                        if (remainingMs <= 0) {
                            stopCountdown();
                            if (examActive) autoSubmit();
                        }
                    }, 500);
                }

                function startHeartbeat() {
                    if (heartbeatTimer) return;
                    const send = async () => {
                        try {
                            await fetch(heartbeatUrl, {
                                method: 'POST',
                                headers: {
                                    'X-CSRF-TOKEN': csrf,
                                    'Accept': 'application/json',
                                    'Content-Type': 'application/json',
                                },
                                body: JSON.stringify({ violations, remaining_ms: Math.max(0, Math.floor(lastRemainingMs || 0)) }),
                            });
                        } catch (e) {}
                    };
                    send();
                    heartbeatTimer = setInterval(send, HEARTBEAT_INTERVAL_MS);
                }

                function stopHeartbeat() {
                    if (heartbeatTimer) { clearInterval(heartbeatTimer); heartbeatTimer = null; }
                }

                // Modal pre-start 5 detik
                const modal = document.getElementById('preflightModal');
                const modalText = document.getElementById('preflightText');
                const modalCount = document.getElementById('preflightCount');
                const modalConfirm = document.getElementById('preflightConfirm');
                function openPreflight(mode) {
                    if (locked) return;
                    preflightMode = mode;
                    if (!modal) { if (mode==='cat') startExam(); else startDocxExam(); return; }
                    modal.classList.remove('hidden');
                    modalConfirm.disabled = true;
                    let c = 5;
                    modalCount.textContent = c;
                    modalText.textContent = 'Tetap di halaman ini selama ujian. Jika keluar layar/tab, ujian otomatis selesai.';
                    const t = setInterval(() => {
                        c -= 1; modalCount.textContent = c;
                        if (c <= 0) {
                            clearInterval(t);
                            modalConfirm.disabled = false;
                            modalConfirm.focus();
                        }
                    }, 1000);
                    modalConfirm.onclick = () => {
                        modal.classList.add('hidden');
                        if (preflightMode === 'cat') startExam(); else startDocxExam();
                    };
                }

                // Modal konfirmasi keluar/selesai
                const confirmExitModal = document.getElementById('confirmExitModal');
                const confirmExitCancel = document.getElementById('confirmExitCancel');
                const confirmExitConfirm = document.getElementById('confirmExitConfirm');
                function openConfirmExit() {
                    if (!examActive) return; // hanya saat ujian aktif
                    if (!confirmExitModal) { autoSubmit(); return; }
                    confirmExitModal.classList.remove('hidden');
                    // fokus ke tombol konfirmasi untuk aksesibilitas
                    if (confirmExitConfirm) confirmExitConfirm.focus();
                }
                if (confirmExitCancel) {
                    confirmExitCancel.addEventListener('click', () => {
                        confirmExitModal.classList.add('hidden');
                    });
                }
                if (confirmExitConfirm) {
                    confirmExitConfirm.addEventListener('click', async () => {
                        confirmExitModal.classList.add('hidden');
                        await finishExam();
                    });
                }

                document.addEventListener('visibilitychange', () => {
                    if (document.hidden && examActive) {
                        violations++;
                        console.warn('Exam focus lost. Violations:', violations);
                        warn('Fokus hilang dari halaman ujian. Harap tetap di sini.');
                        if (violations >= VIOLATION_THRESHOLD) {
                            autoSubmit();
                        }
                    }
                });

                window.addEventListener('blur', () => {
                    if (examActive) {
                        violations++;
                        console.warn('Window blurred. Violations:', violations);
                        warn('Jendela kehilangan fokus saat ujian.');
                        if (violations >= VIOLATION_THRESHOLD) {
                            autoSubmit();
                        }
                    }
                });

                document.addEventListener('fullscreenchange', () => {
                    if (!document.fullscreenElement && examActive) {
                        violations++;
                        warn('Keluar dari layar penuh saat ujian.');
                        if (violations >= VIOLATION_THRESHOLD) {
                            autoSubmit();
                        }
                    }
                });

                // Blok klik kanan
                window.addEventListener('contextmenu', (e) => {
                    if (examActive) {
                        e.preventDefault();
                        warn('Klik kanan dinonaktifkan selama ujian.');
                    }
                });

                // Blok beberapa pintasan umum
                window.addEventListener('keydown', (e) => {
                    if (!examActive) return;
                    const key = e.key.toLowerCase();
                    const combo = {
                        ctrl: e.ctrlKey, shift: e.shiftKey, alt: e.altKey, meta: e.metaKey
                    };
                    const blocked = (
                        key === 'f12' ||
                        (combo.ctrl && ['c','v','x','s','p','f','u'].includes(key)) ||
                        (combo.ctrl && combo.shift && ['i','c','j'].includes(key)) ||
                        (combo.ctrl && key === 'tab')
                    );
                    if (blocked) {
                        e.preventDefault();
                        e.stopPropagation();
                        warn('Pintasan keyboard dinonaktifkan selama ujian.');
                    }
                }, { capture: true });

                // Konfirmasi saat mencoba keluar
                window.addEventListener('beforeunload', (e) => {
                    if (examActive) {
                        e.preventDefault();
                        e.returnValue = '';
                        return '';
                    }
                });

                if (btnStart && !locked) btnStart.addEventListener('click', () => openPreflight('cat'));
                if (btnExit) btnExit.addEventListener('click', openConfirmExit);
                if (btnStartDocx && !locked) btnStartDocx.addEventListener('click', () => openPreflight('docx'));
                if (btnExitDocx) btnExitDocx.addEventListener('click', openConfirmExit);
                if (btnExportCatDocx) {
                    btnExportCatDocx.addEventListener('click', async () => {
                        const text = (catAnswerText ? catAnswerText.value : '').trim();
                        if (!text || text.length < 1) { warn('Isi jawaban/ catatan sebelum mengunduh DOCX.'); return; }
                        try {
                            const fd = new FormData();
                            fd.append('answer_text', text);
                            const res = await fetch('{{ route('murid.exams.export_docx', $exam) }}', {
                                method: 'POST',
                                headers: { 'X-CSRF-TOKEN': csrf },
                                body: fd,
                            });
                            if (!res.ok) { warn('Gagal membuat DOCX.'); return; }
                            const blob = await res.blob();
                            const url = URL.createObjectURL(blob);
                            const a = document.createElement('a');
                            a.href = url;
                            a.download = 'Jawaban_{{ preg_replace('/[^A-Za-z0-9_\-]/', '_', $exam->title) }}.docx';
                            document.body.appendChild(a);
                            a.click();
                            a.remove();
                            URL.revokeObjectURL(url);
                        } catch (e) { warn('Terjadi kesalahan saat mengunduh DOCX.'); }
                    });
                }
                // Handle Multiple Choice Exam Security
                const mcExamIntro = document.getElementById('mc-exam-intro');
                const mcExamWrapper = document.getElementById('mc-exam-wrapper');
                const btnStartMcExam = document.getElementById('btn-start-mc-exam');
                let mcExamActive = false;
                let mcViolations = 0;
                const MC_VIOLATION_THRESHOLD = 1;

                if (btnStartMcExam && !locked) {
                    btnStartMcExam.addEventListener('click', function() {
                        if (confirm('Ujian akan dimulai dalam mode layar penuh. Jangan keluar dari halaman ini sampai selesai. Lanjutkan?')) {
                            startMcExam();
                        }
                    });
                }

                function startMcExam() {
                    mcExamActive = true;
                    mcViolations = 0;
                    
                    // Request fullscreen
                    const el = document.documentElement;
                    if (el.requestFullscreen) {
                        el.requestFullscreen().catch(() => {
                            alert('Gagal masuk ke mode layar penuh. Pastikan izinkan fullscreen.');
                        });
                    }
                    
                    // Hide intro, show form
                    if (mcExamIntro) mcExamIntro.classList.add('hidden');
                    if (mcExamWrapper) mcExamWrapper.classList.remove('hidden');
                    
                    // Warn user
                    warn('Ujian dimulai. Jangan keluar dari halaman ini!');
                }

                function exitMcExam() {
                    mcExamActive = false;
                    if (document.exitFullscreen) {
                        document.exitFullscreen();
                    }
                }

                // Detect violations for MC exam
                if (!locked) {
                    document.addEventListener('visibilitychange', () => {
                        if (document.hidden && mcExamActive) {
                            mcViolations++;
                            console.warn('MC Exam focus lost. Violations:', mcViolations);
                            warn('Fokus hilang dari halaman ujian. Harap tetap di sini.');
                            if (mcViolations >= MC_VIOLATION_THRESHOLD) {
                                autoFinishMcExam();
                            }
                        }
                    });

                    window.addEventListener('blur', () => {
                        if (mcExamActive) {
                            mcViolations++;
                            console.warn('MC Exam window blurred. Violations:', mcViolations);
                            warn('Jendela kehilangan fokus saat ujian.');
                            if (mcViolations >= MC_VIOLATION_THRESHOLD) {
                                autoFinishMcExam();
                            }
                        }
                    });

                    document.addEventListener('fullscreenchange', () => {
                        if (!document.fullscreenElement && mcExamActive) {
                            mcViolations++;
                            warn('Keluar dari layar penuh saat ujian.');
                            if (mcViolations >= MC_VIOLATION_THRESHOLD) {
                                autoFinishMcExam();
                            }
                        }
                    });

                    // Block right click for MC exam
                    window.addEventListener('contextmenu', (e) => {
                        if (mcExamActive) {
                            e.preventDefault();
                            warn('Klik kanan dinonaktifkan selama ujian.');
                        }
                    });

                    // Block keyboard shortcuts for MC exam
                    window.addEventListener('keydown', (e) => {
                        if (!mcExamActive) return;
                        const key = e.key.toLowerCase();
                        const combo = {
                            ctrl: e.ctrlKey, shift: e.shiftKey, alt: e.altKey, meta: e.metaKey
                        };
                        const blocked = (
                            key === 'f12' ||
                            (combo.ctrl && ['c','v','x','s','p','f','u','t','w','n'].includes(key)) ||
                            (combo.ctrl && combo.shift && ['i','c','j','t','n'].includes(key)) ||
                            (combo.ctrl && key === 'tab') ||
                            (combo.alt && ['tab','f4'].includes(key))
                        );
                        if (blocked) {
                            e.preventDefault();
                            e.stopPropagation();
                            warn('Pintasan keyboard dinonaktifkan selama ujian.');
                        }
                    }, { capture: true });

                    // Prevent leaving page
                    window.addEventListener('beforeunload', (e) => {
                        if (mcExamActive) {
                            e.preventDefault();
                            e.returnValue = '';
                            return '';
                        }
                    });
                }

                async function autoFinishMcExam() {
                    if (!mcExamActive) return;
                    mcExamActive = false;
                    try {
                        await fetch(autoFinishUrl, {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': csrf,
                                'Accept': 'application/json',
                            },
                        });
                    } catch (e) {}
                    exitMcExam();
                    warn('Ujian otomatis selesai karena melanggar aturan. Nilai dikosongkan.');
                    if (mcExamWrapper) {
                        const banner = document.createElement('div');
                        banner.className = 'rounded-xl bg-red-50 border border-red-100 p-4 mb-4';
                        banner.innerHTML = '<p class="text-sm text-red-700">Ujian telah berakhir otomatis karena Anda melanggar aturan ujian.</p>';
                        mcExamWrapper.insertBefore(banner, mcExamWrapper.firstChild);
                    }
                    if (btnStartMcExam) {
                        btnStartMcExam.disabled = true;
                        btnStartMcExam.classList.add('opacity-50', 'cursor-not-allowed');
                    }
                }

                // Exit button for MC exam
                const btnExitMcExam = document.getElementById('btn-exit-mc-exam');
                if (btnExitMcExam) {
                    btnExitMcExam.addEventListener('click', function() {
                        if (confirm('Yakin ingin keluar dari ujian? Ujian akan diakhiri.')) {
                            exitMcExam();
                            if (mcExamIntro) mcExamIntro.classList.remove('hidden');
                            if (mcExamWrapper) mcExamWrapper.classList.add('hidden');
                            mcExamActive = false;
                        }
                    });
                }

                // Handle form pilihan ganda
                const multipleChoiceForm = document.getElementById('multiple-choice-form');
                if (multipleChoiceForm && !{{ $examLocked ? 'true' : 'false' }}) {
                    multipleChoiceForm.addEventListener('submit', async function(e) {
                        e.preventDefault();
                        
                        if (finishedOnce || !mcExamActive) return;
                        
                        // Confirm before finishing
                        if (!confirm('Yakin ingin menyelesaikan ujian? Setelah selesai, Anda tidak bisa mengubah jawaban.')) {
                            return;
                        }
                        
                        const formData = new FormData(this);
                        const answers = {};
                        formData.forEach((value, key) => {
                            if (key.startsWith('answers[')) {
                                const match = key.match(/answers\[(\d+)\]/);
                                if (match) {
                                    answers[match[1]] = value;
                                }
                            }
                        });
                        
                        // Validasi semua soal sudah dijawab
                        const totalQuestions = {{ count($questions) }};
                        const answeredCount = Object.keys(answers).length;
                        if (answeredCount < totalQuestions) {
                            if (!confirm('Anda belum menjawab semua soal (' + answeredCount + '/' + totalQuestions + '). Yakin ingin menyelesaikan ujian?')) {
                                return;
                            }
                        }
                        
                        finishedOnce = true;
                        
                        try {
                            const submitData = new FormData();
                            Object.keys(answers).forEach(index => {
                                submitData.append(`answers[${index}]`, answers[index]);
                            });
                            
                            const response = await fetch(finishUrl, {
                                method: 'POST',
                                headers: { 
                                    'X-CSRF-TOKEN': csrf,
                                    'Accept': 'application/json',
                                },
                                body: submitData,
                            });
                            
                            const result = await response.json();
                            
                            if (response.ok) {
                                exitMcExam();
                                const scoreMsg = result.score !== null && result.score !== undefined 
                                    ? 'Nilai: ' + parseFloat(result.score).toFixed(2) 
                                    : 'Menunggu penilaian';
                                alert('Ujian selesai! ' + scoreMsg);
                                window.location.reload();
                            } else {
                                alert('Terjadi kesalahan saat menyelesaikan ujian.');
                                finishedOnce = false;
                            }
                        } catch (e) {
                            console.error('Error submitting multiple choice:', e);
                            alert('Terjadi kesalahan saat menyelesaikan ujian.');
                            finishedOnce = false;
                        }
                    });
                }

                if (locked) {
                    if (btnStart) { btnStart.disabled = true; btnStart.classList.add('opacity-50','cursor-not-allowed'); }
                    if (btnStartDocx) { btnStartDocx.disabled = true; btnStartDocx.classList.add('opacity-50','cursor-not-allowed'); }
                }
                // Mulai countdown pasca-load agar pengguna melihat waktu berjalan meski belum menekan "Mulai"
                if (initialRemainingMsServer > 0) startCountdown();
            })();
        </script>

        <!-- Modal pre-start 5 detik -->
        <div id="preflightModal" class="fixed inset-0 hidden bg-black/40 z-50">
            <div class="absolute inset-0 flex items-center justify-center p-4">
                <div class="w-full max-w-lg bg-white rounded-xl shadow-xl border border-emerald-100">
                    <div class="px-4 py-3 border-b border-emerald-100">
                        <p class="text-emerald-900 font-semibold">Peringatan Ujian</p>
                    </div>
                    <div class="p-4 space-y-2">
                        <p id="preflightText" class="text-sm text-emerald-700"></p>
                        <p class="text-sm text-emerald-700">Mulai dalam <span id="preflightCount" class="font-semibold">5</span> detik…</p>
                    </div>
                    <div class="px-4 py-3 border-t border-emerald-100 flex justify-end gap-2">
                        <button type="button" class="px-3 py-2 rounded-lg bg-emerald-100 text-emerald-700 hover:bg-emerald-200" onclick="(function(){const m=document.getElementById('preflightModal'); if(m) m.classList.add('hidden');})()">Batal</button>
                        <button id="preflightConfirm" type="button" class="px-3 py-2 rounded-lg bg-emerald-600 text-white hover:bg-emerald-700">Saya mengerti, mulai</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal konfirmasi selesai -->
        <div id="confirmExitModal" class="fixed inset-0 hidden bg-black/40 z-50">
            <div class="absolute inset-0 flex items-center justify-center p-4">
                <div class="w-full max-w-md bg-white rounded-xl shadow-xl border border-emerald-100">
                    <div class="px-4 py-3 border-b border-emerald-100">
                        <p class="text-emerald-900 font-semibold">Konfirmasi Selesai Ujian</p>
                    </div>
                    <div class="p-4 space-y-2">
                        <p class="text-sm text-emerald-700">Anda yakin ingin mengakhiri ujian dan menyimpan jawaban?</p>
                        <p class="text-xs text-emerald-600">Pastikan jawaban sudah benar. Setelah ini ujian akan ditutup.</p>
                    </div>
                    <div class="px-4 py-3 border-t border-emerald-100 flex justify-end gap-2">
                        <button id="confirmExitCancel" type="button" class="px-3 py-2 rounded-lg bg-emerald-100 text-emerald-700 hover:bg-emerald-200">Batal</button>
                        <button id="confirmExitConfirm" type="button" class="px-3 py-2 rounded-lg bg-emerald-600 text-white hover:bg-emerald-700">Ya, Akhiri</button>
                    </div>
                </div>
            </div>
        </div>
        </div>
    </div>
@endsection

