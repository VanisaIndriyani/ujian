<div class="grid md:grid-cols-2 gap-5">
    <div>
        <label class="block text-sm font-medium text-emerald-600">Kode Mata Kuliah</label>
        <input type="text" name="code" value="{{ old('code', $subject->code ?? '') }}" class="mt-1 w-full rounded-xl border border-emerald-200 px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-transparent" required>
    </div>
    <div>
        <label class="block text-sm font-medium text-emerald-600">Nama Mata Kuliah</label>
        <input type="text" name="name" value="{{ old('name', $subject->name ?? '') }}" class="mt-1 w-full rounded-xl border border-emerald-200 px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-transparent" required>
    </div>
    <div>
        <label class="block text-sm font-medium text-emerald-600">Semester</label>
        <select name="semester" class="mt-1 w-full rounded-xl border border-emerald-200 px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-transparent" required>
            <option value="">— Pilih Semester —</option>
            @for ($i = 1; $i <= 8; $i++)
                <option value="{{ $i }}" {{ (int) old('semester', $subject->semester ?? 0) === $i ? 'selected' : '' }}>Semester {{ $i }}</option>
            @endfor
        </select>
    </div>
    <div>
        <label class="block text-sm font-medium text-emerald-600">Jumlah SKS</label>
        <input type="number" name="sks" value="{{ old('sks', $subject->sks ?? '') }}" min="1" max="10" class="mt-1 w-full rounded-xl border border-emerald-200 px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-transparent" required>
    </div>
    <div class="md:col-span-2">
        <label class="block text-sm font-medium text-emerald-600">Dosen Pengampu</label>
        <select name="guru_id" class="mt-1 w-full rounded-xl border border-emerald-200 px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-transparent">
            <option value="">— Pilih Dosen —</option>
            @foreach ($gurus as $guru)
                <option value="{{ $guru->id }}" {{ (int) old('guru_id', $subject->guru_id ?? 0) === $guru->id ? 'selected' : '' }}>{{ $guru->name }} ({{ $guru->nip ?? 'NIP tidak tersedia' }})</option>
            @endforeach
        </select>
    </div>
</div>

