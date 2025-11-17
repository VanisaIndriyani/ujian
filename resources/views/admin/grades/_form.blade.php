<div class="grid md:grid-cols-2 gap-5">
    <div>
        <label class="block text-sm font-medium text-emerald-600">Mahasiswa</label>
        <select name="student_id" class="mt-1 w-full rounded-xl border border-emerald-200 px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-transparent" required>
            <option value="">— Pilih Mahasiswa —</option>
            @foreach ($students as $student)
                <option value="{{ $student->id }}" {{ (int) old('student_id', $grade->student_id ?? 0) === $student->id ? 'selected' : '' }}>
                    {{ $student->name }} ({{ $student->nisn ?? 'NIM -' }})
                </option>
            @endforeach
        </select>
    </div>

    <div>
<label class="block text-sm font-medium text-emerald-600">Mata Kuliah</label>
        <select name="subject_id" class="mt-1 w-full rounded-xl border border-emerald-200 px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-transparent" required>
<option value="">— Pilih Mata Kuliah —</option>
            @foreach ($subjects as $subject)
                <option value="{{ $subject->id }}" {{ (int) old('subject_id', $grade->subject_id ?? 0) === $subject->id ? 'selected' : '' }}>
                    {{ $subject->name }}
                </option>
            @endforeach
        </select>
    </div>

    <div>
        <label class="block text-sm font-medium text-emerald-600">Semester</label>
        <input type="text" name="semester" value="{{ old('semester', $grade->semester ?? 'Ganjil') }}" class="mt-1 w-full rounded-xl border border-emerald-200 px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-transparent" required>
    </div>

    <div>
        <label class="block text-sm font-medium text-emerald-600">Nilai (0-100)</label>
        <input type="number" step="0.01" name="score" value="{{ old('score', $grade->score ?? 0) }}" class="mt-1 w-full rounded-xl border border-emerald-200 px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-transparent" required>
    </div>

    <div class="md:col-span-2">
        <label class="block text-sm font-medium text-emerald-600">Catatan</label>
        <textarea name="notes" rows="3" class="mt-1 w-full rounded-xl border border-emerald-200 px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-transparent" placeholder="Opsional">{{ old('notes', $grade->notes ?? '') }}</textarea>
    </div>
</div>

