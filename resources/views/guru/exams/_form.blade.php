<div class="grid md:grid-cols-2 gap-5">
    <div>
<label class="block text-sm font-medium text-emerald-600">Mata Kuliah</label>
<select name="subject_id" class="mt-1 w-full rounded-xl border border-emerald-200 px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-transparent" required>
            <option value="">— Pilih Mata Kuliah —</option>
            @foreach ($subjects as $subject)
                <option value="{{ $subject->id }}" {{ (int) old('subject_id', $exam->subject_id ?? null) === $subject->id ? 'selected' : '' }}>
                    {{ $subject->name }}
                </option>
            @endforeach
        </select>
    </div>

    <div>
        <label class="block text-sm font-medium text-emerald-600">Semester</label>
        <select name="semester" class="mt-1 w-full rounded-xl border border-emerald-200 px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-transparent" required>
            <option value="">— Pilih Semester —</option>
            @for ($i = 1; $i <= 8; $i++)
                <option value="{{ $i }}" {{ (int) old('semester', $exam->semester ?? 0) === $i ? 'selected' : '' }}>Semester {{ $i }}</option>
            @endfor
        </select>
    </div>

    <div>
        <label class="block text-sm font-medium text-emerald-600">Jenis Ujian</label>
        <select name="type" class="mt-1 w-full rounded-xl border border-emerald-200 px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-transparent">
            <option value="">— Pilih Jenis —</option>
            <option value="UTS" {{ old('type', $exam->type ?? '') === 'UTS' ? 'selected' : '' }}>UTS</option>
            <option value="UAS" {{ old('type', $exam->type ?? '') === 'UAS' ? 'selected' : '' }}>UAS</option>
        </select>
    </div>

    <div>
        <label class="block text-sm font-medium text-emerald-600">Jurusan</label>
        <select name="classroom" class="mt-1 w-full rounded-xl border border-emerald-200 px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-transparent" required>
            <option value="">— Pilih Jurusan —</option>
            @isset($classrooms)
                @foreach ($classrooms as $room)
                    <option value="{{ $room }}" {{ old('classroom', $exam->classroom ?? '') === $room ? 'selected' : '' }}>{{ $room }}</option>
                @endforeach
            @endisset
        </select>
        <p class="mt-1 text-xs text-emerald-400">Wajib pilih Jurusan karena soal berbeda tiap jurusan.</p>
    </div>

    <div>
<label class="block text-sm font-medium text-emerald-600">Judul Ujian Matakuliah</label>
<input type="text" name="title" value="{{ old('title', $exam->title ?? '') }}" class="mt-1 w-full rounded-xl border border-emerald-200 px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-transparent" required>
    </div>

    <div class="md:col-span-2">
<label class="block text-sm font-medium text-emerald-600">Deskripsi</label>
<textarea name="description" rows="4" class="mt-1 w-full rounded-xl border border-emerald-200 px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-transparent" placeholder="Aturan dan materi ujian matakuliah">{{ old('description', $exam->description ?? '') }}</textarea>
    </div>

  

    <div class="md:col-span-2">
        <label class="block text-sm font-medium text-emerald-600">Berkas Materi/Soal (DOCX/PDF)</label>
        <input type="file" name="material_file" accept=".doc,.docx,.pdf" class="mt-1 w-full rounded-xl border border-emerald-200 px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-transparent">
        @isset($exam)
            @if(!empty($exam->material_path))
                <p class="mt-1 text-xs text-emerald-600">Berkas saat ini: {{ basename($exam->material_path) }}</p>
            @endif
        @endisset
        <p class="mt-1 text-xs text-emerald-400">Opsional. Jika diunggah, murid akan melihat materi langsung di halaman ujian.</p>
    </div>

    <div class="md:col-span-2">
        <div class="border border-emerald-200 rounded-xl p-4 bg-emerald-50/30">
            <label class="block text-sm font-medium text-emerald-600 mb-3">
                <input type="checkbox" id="enable-multiple-choice" name="enable_multiple_choice" value="1" class="mr-2" {{ old('enable_multiple_choice', !empty($exam->questions_json)) ? 'checked' : '' }}>
                Aktifkan Soal Pilihan Ganda (Auto-Grading)
            </label>
            <p class="text-xs text-emerald-500 mb-4">Jika diaktifkan, mahasiswa akan langsung mendapat nilai otomatis setelah selesai mengerjakan.</p>
            
            <div id="multiple-choice-section" class="{{ old('enable_multiple_choice', !empty($exam->questions_json)) ? '' : 'hidden' }} space-y-4">
                <!-- Tab untuk input manual atau bulk -->
                <div class="border-b border-emerald-200 mb-4">
                    <div class="flex gap-2">
                        <button type="button" id="tab-manual" class="px-4 py-2 text-sm font-medium border-b-2 border-emerald-600 text-emerald-700">Input Manual</button>
                        <button type="button" id="tab-bulk" class="px-4 py-2 text-sm font-medium border-b-2 border-transparent text-emerald-600 hover:text-emerald-700">Input Banyak Sekaligus</button>
                    </div>
                </div>

                <!-- Tab Input Manual -->
                <div id="panel-manual" class="space-y-4">
                    <div id="questions-container">
                    @php
                        $questions = old('questions', $exam->questions_json ?? []);
                        $answerKey = old('answer_key', $exam->answer_key_json ?? []);
                    @endphp
                    @if(!empty($questions) && is_array($questions))
                        @foreach($questions as $index => $question)
                            <div class="question-item border border-emerald-200 rounded-lg p-4 bg-white mb-4" data-index="{{ $index }}">
                                <div class="flex items-center justify-between mb-3">
                                    <h4 class="font-medium text-emerald-700">Soal #{{ $index + 1 }}</h4>
                                    <button type="button" class="remove-question text-red-600 hover:text-red-700 text-sm">Hapus</button>
                                </div>
                                <div class="mb-3">
                                    <label class="block text-xs font-medium text-emerald-600 mb-1">Pertanyaan</label>
                                    <textarea name="questions[{{ $index }}][question]" rows="2" class="w-full rounded-lg border border-emerald-200 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500" required>{{ $question['question'] ?? '' }}</textarea>
                                </div>
                                <div class="grid grid-cols-2 gap-2 mb-3">
                                    <div>
                                        <label class="block text-xs font-medium text-emerald-600 mb-1">Opsi A</label>
                                        <input type="text" name="questions[{{ $index }}][options][A]" value="{{ $question['options']['A'] ?? '' }}" class="w-full rounded-lg border border-emerald-200 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500" required>
                                    </div>
                                    <div>
                                        <label class="block text-xs font-medium text-emerald-600 mb-1">Opsi B</label>
                                        <input type="text" name="questions[{{ $index }}][options][B]" value="{{ $question['options']['B'] ?? '' }}" class="w-full rounded-lg border border-emerald-200 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500" required>
                                    </div>
                                    <div>
                                        <label class="block text-xs font-medium text-emerald-600 mb-1">Opsi C</label>
                                        <input type="text" name="questions[{{ $index }}][options][C]" value="{{ $question['options']['C'] ?? '' }}" class="w-full rounded-lg border border-emerald-200 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500" required>
                                    </div>
                                    <div>
                                        <label class="block text-xs font-medium text-emerald-600 mb-1">Opsi D</label>
                                        <input type="text" name="questions[{{ $index }}][options][D]" value="{{ $question['options']['D'] ?? '' }}" class="w-full rounded-lg border border-emerald-200 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500" required>
                                    </div>
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-emerald-600 mb-1">Jawaban Benar</label>
                                    <select name="answer_key[{{ $index }}]" class="w-full rounded-lg border border-emerald-200 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500" required>
                                        <option value="">Pilih Jawaban</option>
                                        <option value="A" {{ ($answerKey[$index] ?? '') === 'A' ? 'selected' : '' }}>A</option>
                                        <option value="B" {{ ($answerKey[$index] ?? '') === 'B' ? 'selected' : '' }}>B</option>
                                        <option value="C" {{ ($answerKey[$index] ?? '') === 'C' ? 'selected' : '' }}>C</option>
                                        <option value="D" {{ ($answerKey[$index] ?? '') === 'D' ? 'selected' : '' }}>D</option>
                                    </select>
                                </div>
                            </div>
                        @endforeach
                    @endif
                </div>
                    <button type="button" id="add-question" class="w-full px-4 py-2 rounded-lg border border-emerald-300 bg-emerald-50 text-emerald-700 hover:bg-emerald-100 text-sm font-medium">
                        + Tambah Soal
                    </button>
                </div>
                </div>

                <!-- Tab Input Banyak Sekaligus -->
                <div id="panel-bulk" class="hidden space-y-4">
                    <div class="bg-emerald-50 border border-emerald-200 rounded-lg p-4">
                        <p class="text-sm font-medium text-emerald-700 mb-2">Format Input:</p>
                        <p class="text-xs text-emerald-600 mb-3">Masukkan soal dengan format berikut (satu soal per baris):</p>
                        <pre class="bg-white border border-emerald-200 rounded p-3 text-xs text-emerald-800 mb-3 overflow-x-auto">Soal 1: Pertanyaan Anda di sini?
A. Opsi A
B. Opsi B
C. Opsi C
D. Opsi D
Jawaban: A

Soal 2: Pertanyaan kedua?
A. Opsi A
B. Opsi B
C. Opsi C
D. Opsi D
Jawaban: B</pre>
                        <textarea id="bulk-questions-input" rows="15" class="w-full rounded-lg border border-emerald-200 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500" placeholder="Paste atau ketik soal di sini..."></textarea>
                        <div class="mt-3 flex items-center justify-between">
                            <p class="text-xs text-emerald-500">Pisahkan setiap soal dengan baris kosong</p>
                            <button type="button" id="import-bulk-questions" class="px-4 py-2 rounded-lg bg-emerald-600 text-white text-sm font-medium hover:bg-emerald-700">
                                Import Soal
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div>
<label class="block text-sm font-medium text-emerald-600">Mulai</label>
<input type="datetime-local" name="start_at" value="{{ old('start_at', optional($exam->start_at ?? null)->format('Y-m-d\\TH:i')) }}" class="mt-1 w-full rounded-xl border border-emerald-200 px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-transparent">
    </div>

    <div>
<label class="block text-sm font-medium text-emerald-600">Selesai</label>
<input type="datetime-local" name="end_at" value="{{ old('end_at', optional($exam->end_at ?? null)->format('Y-m-d\\TH:i')) }}" class="mt-1 w-full rounded-xl border border-emerald-200 px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-transparent">
    </div>
</div>

@push('scripts')
<script>
(function() {
    const enableCheckbox = document.getElementById('enable-multiple-choice');
    const section = document.getElementById('multiple-choice-section');
    const container = document.getElementById('questions-container');
    const addBtn = document.getElementById('add-question');
    
    if (!enableCheckbox || !section || !container || !addBtn) return;
    
    let questionIndex = {{ !empty($questions) && is_array($questions) ? count($questions) : 0 }};
    
    enableCheckbox.addEventListener('change', function() {
        section.classList.toggle('hidden', !this.checked);
    });
    
    addBtn.addEventListener('click', function() {
        const questionHtml = `
            <div class="question-item border border-emerald-200 rounded-lg p-4 bg-white mb-4" data-index="${questionIndex}">
                <div class="flex items-center justify-between mb-3">
                    <h4 class="font-medium text-emerald-700">Soal #${questionIndex + 1}</h4>
                    <button type="button" class="remove-question text-red-600 hover:text-red-700 text-sm">Hapus</button>
                </div>
                <div class="mb-3">
                    <label class="block text-xs font-medium text-emerald-600 mb-1">Pertanyaan</label>
                    <textarea name="questions[${questionIndex}][question]" rows="2" class="w-full rounded-lg border border-emerald-200 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500" required></textarea>
                </div>
                <div class="grid grid-cols-2 gap-2 mb-3">
                    <div>
                        <label class="block text-xs font-medium text-emerald-600 mb-1">Opsi A</label>
                        <input type="text" name="questions[${questionIndex}][options][A]" class="w-full rounded-lg border border-emerald-200 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500" required>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-emerald-600 mb-1">Opsi B</label>
                        <input type="text" name="questions[${questionIndex}][options][B]" class="w-full rounded-lg border border-emerald-200 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500" required>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-emerald-600 mb-1">Opsi C</label>
                        <input type="text" name="questions[${questionIndex}][options][C]" class="w-full rounded-lg border border-emerald-200 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500" required>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-emerald-600 mb-1">Opsi D</label>
                        <input type="text" name="questions[${questionIndex}][options][D]" class="w-full rounded-lg border border-emerald-200 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500" required>
                    </div>
                </div>
                <div>
                    <label class="block text-xs font-medium text-emerald-600 mb-1">Jawaban Benar</label>
                    <select name="answer_key[${questionIndex}]" class="w-full rounded-lg border border-emerald-200 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500" required>
                        <option value="">Pilih Jawaban</option>
                        <option value="A">A</option>
                        <option value="B">B</option>
                        <option value="C">C</option>
                        <option value="D">D</option>
                    </select>
                </div>
            </div>
        `;
        container.insertAdjacentHTML('beforeend', questionHtml);
        questionIndex++;
        updateQuestionNumbers();
    });
    
    container.addEventListener('click', function(e) {
        if (e.target.classList.contains('remove-question')) {
            e.target.closest('.question-item').remove();
            updateQuestionNumbers();
        }
    });
    
    function updateQuestionNumbers() {
        container.querySelectorAll('.question-item').forEach((item, idx) => {
            item.querySelector('h4').textContent = `Soal #${idx + 1}`;
        });
    }

    // Tab switching
    const tabManual = document.getElementById('tab-manual');
    const tabBulk = document.getElementById('tab-bulk');
    const panelManual = document.getElementById('panel-manual');
    const panelBulk = document.getElementById('panel-bulk');

    if (tabManual && tabBulk && panelManual && panelBulk) {
        tabManual.addEventListener('click', function() {
            tabManual.classList.add('border-emerald-600', 'text-emerald-700');
            tabManual.classList.remove('border-transparent', 'text-emerald-600');
            tabBulk.classList.remove('border-emerald-600', 'text-emerald-700');
            tabBulk.classList.add('border-transparent', 'text-emerald-600');
            panelManual.classList.remove('hidden');
            panelBulk.classList.add('hidden');
        });

        tabBulk.addEventListener('click', function() {
            tabBulk.classList.add('border-emerald-600', 'text-emerald-700');
            tabBulk.classList.remove('border-transparent', 'text-emerald-600');
            tabManual.classList.remove('border-emerald-600', 'text-emerald-700');
            tabManual.classList.add('border-transparent', 'text-emerald-600');
            panelBulk.classList.remove('hidden');
            panelManual.classList.add('hidden');
        });
    }

    // Import bulk questions
    const importBtn = document.getElementById('import-bulk-questions');
    const bulkInput = document.getElementById('bulk-questions-input');

    if (importBtn && bulkInput) {
        importBtn.addEventListener('click', function() {
            const text = bulkInput.value.trim();
            if (!text) {
                alert('Masukkan soal terlebih dahulu!');
                return;
            }

            // Parse text menjadi array soal
            const questions = parseBulkQuestions(text);
            
            if (questions.length === 0) {
                alert('Format soal tidak valid. Pastikan mengikuti format yang ditunjukkan.');
                return;
            }

            // Clear existing questions jika user ingin
            if (container.querySelectorAll('.question-item').length > 0) {
                if (!confirm(`Akan menambahkan ${questions.length} soal baru. Lanjutkan?`)) {
                    return;
                }
            }

            // Add questions to container
            questions.forEach(q => {
                const questionHtml = `
                    <div class="question-item border border-emerald-200 rounded-lg p-4 bg-white mb-4" data-index="${questionIndex}">
                        <div class="flex items-center justify-between mb-3">
                            <h4 class="font-medium text-emerald-700">Soal #${questionIndex + 1}</h4>
                            <button type="button" class="remove-question text-red-600 hover:text-red-700 text-sm">Hapus</button>
                        </div>
                        <div class="mb-3">
                            <label class="block text-xs font-medium text-emerald-600 mb-1">Pertanyaan</label>
                            <textarea name="questions[${questionIndex}][question]" rows="2" class="w-full rounded-lg border border-emerald-200 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500" required>${escapeHtml(q.question)}</textarea>
                        </div>
                        <div class="grid grid-cols-2 gap-2 mb-3">
                            <div>
                                <label class="block text-xs font-medium text-emerald-600 mb-1">Opsi A</label>
                                <input type="text" name="questions[${questionIndex}][options][A]" value="${escapeHtml(q.options.A)}" class="w-full rounded-lg border border-emerald-200 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500" required>
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-emerald-600 mb-1">Opsi B</label>
                                <input type="text" name="questions[${questionIndex}][options][B]" value="${escapeHtml(q.options.B)}" class="w-full rounded-lg border border-emerald-200 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500" required>
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-emerald-600 mb-1">Opsi C</label>
                                <input type="text" name="questions[${questionIndex}][options][C]" value="${escapeHtml(q.options.C)}" class="w-full rounded-lg border border-emerald-200 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500" required>
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-emerald-600 mb-1">Opsi D</label>
                                <input type="text" name="questions[${questionIndex}][options][D]" value="${escapeHtml(q.options.D)}" class="w-full rounded-lg border border-emerald-200 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500" required>
                            </div>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-emerald-600 mb-1">Jawaban Benar</label>
                            <select name="answer_key[${questionIndex}]" class="w-full rounded-lg border border-emerald-200 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500" required>
                                <option value="">Pilih Jawaban</option>
                                <option value="A" ${q.answer === 'A' ? 'selected' : ''}>A</option>
                                <option value="B" ${q.answer === 'B' ? 'selected' : ''}>B</option>
                                <option value="C" ${q.answer === 'C' ? 'selected' : ''}>C</option>
                                <option value="D" ${q.answer === 'D' ? 'selected' : ''}>D</option>
                            </select>
                        </div>
                    </div>
                `;
                container.insertAdjacentHTML('beforeend', questionHtml);
                questionIndex++;
            });

            updateQuestionNumbers();
            
            // Switch to manual tab to see imported questions
            if (tabManual) tabManual.click();
            
            // Clear bulk input
            bulkInput.value = '';
            
            alert(`Berhasil mengimport ${questions.length} soal!`);
        });
    }

    function parseBulkQuestions(text) {
        const questions = [];
        // Split by double newlines (soal dipisahkan baris kosong)
        const blocks = text.split(/\n\s*\n/).filter(b => b.trim());
        
        blocks.forEach(block => {
            const lines = block.split('\n').map(l => l.trim()).filter(l => l);
            if (lines.length < 6) return; // Minimal: 1 pertanyaan + 4 opsi + 1 jawaban
            
            let question = '';
            const options = { A: '', B: '', C: '', D: '' };
            let answer = '';
            
            let i = 0;
            // Parse question (bisa dimulai dengan "Soal X:" atau langsung pertanyaan)
            if (lines[i].match(/^Soal\s+\d+:/i)) {
                question = lines[i].replace(/^Soal\s+\d+:\s*/i, '');
                i++;
            } else {
                question = lines[i];
                i++;
            }
            
            // Parse options (A., B., C., D.)
            const optionPattern = /^([A-D])[\.\)]\s*(.+)$/i;
            while (i < lines.length && optionPattern.test(lines[i])) {
                const match = lines[i].match(optionPattern);
                if (match) {
                    const letter = match[1].toUpperCase();
                    options[letter] = match[2];
                }
                i++;
            }
            
            // Parse answer (Jawaban: A atau Jawaban A)
            while (i < lines.length) {
                const answerMatch = lines[i].match(/Jawaban\s*:?\s*([A-D])/i);
                if (answerMatch) {
                    answer = answerMatch[1].toUpperCase();
                    break;
                }
                i++;
            }
            
            // Validate
            if (question && options.A && options.B && options.C && options.D && answer) {
                questions.push({ question, options, answer });
            }
        });
        
        return questions;
    }

    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }
})();
</script>
@endpush

