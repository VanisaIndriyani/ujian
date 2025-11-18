@php
    $isEdit = isset($user);
    $selectedRole = old('role', $user->role ?? request('role', 'guru'));
@endphp

<div class="grid md:grid-cols-2 gap-5">
    <div>
        <label class="block text-sm font-medium text-emerald-600">Nama Lengkap</label>
        <input type="text" name="name" value="{{ old('name', $user->name ?? '') }}" class="mt-1 w-full rounded-xl border border-emerald-200 px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-transparent" required>
    </div>

    <div>
        <label class="block text-sm font-medium text-emerald-600">Role</label>
        <select name="role" class="mt-1 w-full rounded-xl border border-emerald-200 px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-transparent" {{ $isEdit ? 'disabled' : '' }}>
            <option value="guru" {{ $selectedRole === 'guru' ? 'selected' : '' }}>Guru</option>
            <option value="murid" {{ $selectedRole === 'murid' ? 'selected' : '' }}>Mahasiswa</option>
        </select>
        @if ($isEdit)
            <input type="hidden" name="role" value="{{ $user->role }}">
        @endif
    </div>

    <div>
        <label class="block text-sm font-medium text-emerald-600">Email</label>
        <input type="email" name="email" value="{{ old('email', $user->email ?? '') }}" class="mt-1 w-full rounded-xl border border-emerald-200 px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-transparent" placeholder="Opsional">
    </div>

    <div>
        <label class="block text-sm font-medium text-emerald-600">NIP (Guru)</label>
        <input type="text" name="nip" value="{{ old('nip', $user->nip ?? '') }}" class="mt-1 w-full rounded-xl border border-emerald-200 px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-transparent" placeholder="Isi untuk role guru">
    </div>

    <div>
        <label class="block text-sm font-medium text-emerald-600">NIM (Mahasiswa)</label>
        <input type="text" name="nisn" value="{{ old('nisn', $user->nisn ?? '') }}" class="mt-1 w-full rounded-xl border border-emerald-200 px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-transparent" placeholder="Isi untuk role mahasiswa">
    </div>

    <div>
        <label class="block text-sm font-medium text-emerald-600">Kelas (Mahasiswa)</label>
        <select name="classroom" class="mt-1 w-full rounded-xl border border-emerald-200 px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-transparent">
            <option value="">— Pilih Kelas —</option>
            @isset($classrooms)
                @foreach ($classrooms as $room)
                    <option value="{{ $room->name }}" {{ old('classroom', $user->classroom ?? '') === $room->name ? 'selected' : '' }}>
                        {{ $room->name }}
                    </option>
                @endforeach
            @endisset
        </select>
        <p class="text-xs text-emerald-400 mt-1">
            Tidak ada pilihan yang cocok? 
            <a href="{{ route('admin.classrooms.create') }}" class="underline hover:text-emerald-600">Tambah Kelas</a>
        </p>
    </div>

    <div>
        <label class="block text-sm font-medium text-emerald-600">Kata Sandi</label>
        <div class="relative">
            <input type="password" name="password" class="mt-1 w-full rounded-xl border border-emerald-200 px-4 py-2.5 pr-10 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-transparent">
            <button type="button" class="absolute right-3 top-1/2 -translate-y-1/2 text-emerald-600" data-password-toggle aria-label="Tampilkan/Sembunyikan Password">
                <i class="fa-solid fa-eye"></i>
            </button>
        </div>
        <p class="text-xs text-emerald-400 mt-1">{{ $isEdit ? 'Kosongkan jika tidak ingin mengubah password.' : 'Jika dikosongkan akan digenerate otomatis.' }}</p>
        
    </div>

    <div class="md:col-span-2">
        <label class="block text-sm font-medium text-emerald-600">Foto Profil</label>
        <input type="file" name="photo" accept="image/*" class="mt-1 w-full rounded-xl border border-emerald-200 px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-transparent">
        @if ($isEdit ?? false)
            <div class="mt-3 flex items-center gap-3">
                <img src="{{ $user->profile_photo_url }}" alt="Foto saat ini" class="h-16 w-16 rounded-xl object-cover ring-2 ring-emerald-100">
                <p class="text-xs text-emerald-400">Jika mengunggah foto baru, foto lama akan diganti.</p>
            </div>
        @endif
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', () => {
        document.querySelectorAll('[data-password-toggle]').forEach(btn => {
            const input = btn.closest('div').querySelector('input[name="password"]');
            let visible = false;
            btn.addEventListener('click', () => {
                visible = !visible;
                input.type = visible ? 'text' : 'password';
                btn.innerHTML = visible ? '<i class="fa-solid fa-eye-slash"></i>' : '<i class="fa-solid fa-eye"></i>';
            });
        });
    });
</script>
@endpush

