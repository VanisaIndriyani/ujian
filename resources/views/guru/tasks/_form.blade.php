<div class="grid md:grid-cols-2 gap-5">
    <div>
<label class="block text-sm font-medium text-emerald-600">Mata Kuliah</label>
<select name="subject_id" class="mt-1 w-full rounded-xl border border-emerald-200 px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-transparent" required>
            <option value="">— Pilih Mata Kuliah —</option>
            @foreach ($subjects as $subject)
                <option value="{{ $subject->id }}" {{ (int) old('subject_id', $task->subject_id ?? 0) === $subject->id ? 'selected' : '' }}>
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
                <option value="{{ $i }}" {{ (int) old('semester', $task->semester ?? 0) === $i ? 'selected' : '' }}>Semester {{ $i }}</option>
            @endfor
        </select>
    </div>

    <div>
        <label class="block text-sm font-medium text-emerald-600">Jurusan</label>
        <select name="classroom" class="mt-1 w-full rounded-xl border border-emerald-200 px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-transparent">
            <option value="">— Semua Jurusan —</option>
            @isset($classrooms)
                @foreach ($classrooms as $room)
                    <option value="{{ $room }}" {{ (old('classroom', $task->classroom ?? '') === $room) ? 'selected' : '' }}>{{ $room }}</option>
                @endforeach
            @endisset
        </select>
    </div>

    <div>
<label class="block text-sm font-medium text-emerald-600">Judul Tugas</label>
<input type="text" name="title" value="{{ old('title', $task->title ?? '') }}" class="mt-1 w-full rounded-xl border border-emerald-200 px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-transparent" required>
    </div>

    <div class="md:col-span-2">
<label class="block text-sm font-medium text-emerald-600">Deskripsi</label>
<textarea name="description" rows="4" class="mt-1 w-full rounded-xl border border-emerald-200 px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-transparent" placeholder="Detail instruksi tugas">{{ old('description', $task->description ?? '') }}</textarea>
    </div>

    <div>
<label class="block text-sm font-medium text-emerald-600">Tenggat</label>
<input type="datetime-local" name="due_at" value="{{ old('due_at', optional($task->due_at ?? null)?->format('Y-m-d\TH:i')) }}" class="mt-1 w-full rounded-xl border border-emerald-200 px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-transparent">
    </div>
</div>

