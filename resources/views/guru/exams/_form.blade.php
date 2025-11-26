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

    <div>
<label class="block text-sm font-medium text-emerald-600">Mulai</label>
<input type="datetime-local" name="start_at" value="{{ old('start_at', optional($exam->start_at ?? null)->format('Y-m-d\\TH:i')) }}" class="mt-1 w-full rounded-xl border border-emerald-200 px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-transparent">
    </div>

    <div>
<label class="block text-sm font-medium text-emerald-600">Selesai</label>
<input type="datetime-local" name="end_at" value="{{ old('end_at', optional($exam->end_at ?? null)->format('Y-m-d\\TH:i')) }}" class="mt-1 w-full rounded-xl border border-emerald-200 px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-transparent">
    </div>
</div>

