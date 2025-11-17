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

            @if ($exam->material_path)
                <div class="border-t border-emerald-100 pt-4 space-y-3">
                    <p class="text-xs uppercase tracking-wide text-emerald-400">Materi/Soal Ujian</p>
                    @php
                        $ext = strtolower(pathinfo($exam->material_path, PATHINFO_EXTENSION));
                        // Normalisasi path agar mendukung nilai seperti "exams/materials/...", "public/exams/...", atau "storage/exams/..."
                        $raw = ltrim($exam->material_path, '/');
                        $normalized = preg_replace('#^(storage/|public/)#', '', $raw);
                        $url = \Illuminate\Support\Facades\Storage::url($normalized);
                    @endphp
                    @if ($ext === 'pdf')
                        <iframe src="{{ $url }}#toolbar=0" class="w-full h-[78vh] bg-white" referrerpolicy="no-referrer"></iframe>
                    @else
                        <div class="rounded-xl border border-emerald-100 p-4 bg-emerald-50">
                            <p class="text-sm text-emerald-700">Berkas Word terlampir.</p>
                            <p class="text-xs text-emerald-500 mt-2">Mode editor web akan menampilkan isi DOCX di halaman ini, meminta layar penuh, dan otomatis mengakhiri ujian jika Anda berpindah aplikasi/tab.</p>

                            <div class="mt-3 space-x-2">
                                <button id="btn-start-docx" type="button" class="inline-flex items-center gap-2 px-3 py-2 rounded-lg bg-emerald-600 text-white text-xs hover:bg-emerald-700">Mulai Ujian (Dokumen)</button>
                                <a href="{{ $url }}" target="_blank" class="inline-flex items-center gap-2 px-3 py-2 rounded-lg bg-emerald-500 text-white text-xs hover:bg-emerald-600">(Cadangan) Unduh DOCX</a>
                            </div>

                            <div id="docx-wrapper" class="hidden mt-4 border border-emerald-100 rounded-xl overflow-hidden">
                                <div class="flex items-center justify-between px-3 py-2 bg-emerald-50 border-b border-emerald-100 text-xs text-emerald-600">
                                    <span>Mode Ujian Dokumen Aktif • Tetap di halaman ini sampai selesai.</span>
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
            @endif
        </div>

        <div class="bg-white border border-emerald-100 rounded-2xl shadow p-6 space-y-3">
            <p class="text-sm text-emerald-600">Catatan: Untuk keamanan ujian, sistem akan:
            </p>
            <ul class="list-disc pl-5 text-sm text-emerald-600">
                <li>Meminta layar penuh saat ujian dibuka.</li>
                <li>Mendeteksi jika jendela/tab kehilangan fokus dan menampilkan peringatan.</li>
                <li>Mencatat pelanggaran (keluar dari fullscreen, berpindah tab) ke konsol.</li>
            </ul>
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
                const docxWrapper = document.getElementById('docx-wrapper');
                const docxViewer = document.getElementById('docx-viewer');
                const docxUrl = "{{ isset($url) ? $url : '' }}";
                const csrf = '{{ csrf_token() }}';
                const autoFinishUrl = '{{ route('murid.exams.auto_finish', $exam) }}';
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
                let preflightMode = null;

                const startExam = () => {
                    examActive = true;
                    if (intro) intro.classList.add('hidden');
                    if (frameWrapper) frameWrapper.classList.remove('hidden');
                    if (frameEl) frameEl.src = examUrl;
                    requireFullscreen();
                    warn('Mode ujian aktif. Tetap di halaman ini.');
                    startCountdown();
                };

                const exitExam = () => {
                    examActive = false;
                    if (frameWrapper) frameWrapper.classList.add('hidden');
                    if (intro) intro.classList.remove('hidden');
                    if (docxWrapper) docxWrapper.classList.add('hidden');
                    if (document.fullscreenElement && document.exitFullscreen) {
                        document.exitFullscreen().catch(() => {});
                    }
                    warn('Mode ujian dinonaktifkan.');
                    stopCountdown();
                };

                const startDocxExam = async () => {
                    examActive = true;
                    if (intro) intro.classList.add('hidden');
                    if (docxWrapper) docxWrapper.classList.remove('hidden');
                    requireFullscreen();
                    warn('Mode ujian (Dokumen) aktif. Tetap di halaman ini.');
                    startCountdown();

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
                            if (examActive) autoFinish();
                        }
                    }, 500);
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

                document.addEventListener('visibilitychange', () => {
                    if (document.hidden && examActive) {
                        violations++;
                        console.warn('Exam focus lost. Violations:', violations);
                        autoFinish();
                    }
                });

                window.addEventListener('blur', () => {
                    if (examActive) {
                        violations++;
                        console.warn('Window blurred. Violations:', violations);
                        autoFinish();
                    }
                });

                document.addEventListener('fullscreenchange', () => {
                    if (!document.fullscreenElement && examActive) {
                        autoFinish();
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
                if (btnExit) btnExit.addEventListener('click', exitExam);
                if (btnStartDocx && !locked) btnStartDocx.addEventListener('click', () => openPreflight('docx'));
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
        </div>
    </div>
@endsection

