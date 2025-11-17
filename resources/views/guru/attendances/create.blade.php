@extends('layouts.guru')

@section('title', 'Catat Absensi')
@section('header', 'Catat Absensi Mahasiswa')

@section('content')
    <div class="bg-white border border-emerald-100 rounded-2xl shadow p-6 space-y-6">
        <form method="GET" class="grid md:grid-cols-3 gap-5 text-sm">
            <div>
                <label class="block text-sm font-medium text-emerald-600">Mata Kuliah</label>
                <select name="subject_id" class="mt-1 w-full rounded-xl border border-emerald-200 px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-transparent" required>
                    <option value="">— Pilih Mata Kuliah —</option>
                    @foreach ($subjects as $subject)
                        <option value="{{ $subject->id }}" {{ (int) ($subjectId ?? 0) === $subject->id ? 'selected' : '' }}>
                            {{ $subject->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium text-emerald-600">Jurusan</label>
                <select name="classroom" class="mt-1 w-full rounded-xl border border-emerald-200 px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-transparent">
                    <option value="">— Semua Jurusan —</option>
                    @isset($classrooms)
                        @foreach ($classrooms as $room)
                            <option value="{{ $room }}" {{ ($classroom ?? '') === $room ? 'selected' : '' }}>{{ $room }}</option>
                        @endforeach
                    @endisset
                </select>
            </div>

            <div class="flex items-end">
                <button class="px-4 py-2 rounded-lg bg-emerald-100 text-emerald-600 hover:bg-emerald-200" type="submit">Terapkan</button>
            </div>
        </form>

        @if (!empty($subjectId) && $students->count() > 0)
            <form action="{{ route('guru.attendances.store') }}" method="POST" class="space-y-4">
                @csrf
                <input type="hidden" name="subject_id" value="{{ $subjectId }}">

                <div class="grid md:grid-cols-3 gap-5">
                    <div>
                        <label class="block text-sm font-medium text-emerald-600">Tanggal</label>
                        <input type="date" name="attendance_date" value="{{ old('attendance_date', now()->toDateString()) }}" class="mt-1 w-full rounded-xl border border-emerald-200 px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-transparent" required>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-emerald-600">Status Default</label>
                        <select name="status_default" class="mt-1 w-full rounded-xl border border-emerald-200 px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-transparent">
                            @foreach (['hadir' => 'Hadir', 'izin' => 'Izin', 'sakit' => 'Sakit', 'alpa' => 'Alpa'] as $value => $label)
                                <option value="{{ $value }}" {{ old('status_default', 'hadir') === $value ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="md:col-span-1">
                        <label class="block text-sm font-medium text-emerald-600">Catatan (Opsional)</label>
                        <input type="text" name="notes" value="{{ old('notes') }}" class="mt-1 w-full rounded-xl border border-emerald-200 px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-transparent" placeholder="Misal: Pertemuan ke-3">
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-emerald-100 text-sm">
                        <thead class="bg-emerald-50/60 text-emerald-600 uppercase text-xs tracking-wider">
                            <tr>
                                <th class="px-4 py-3 text-left">
                                    <input type="checkbox" id="select-all" class="rounded border-emerald-300">
                                </th>
                                <th class="px-4 py-3 text-left">Mahasiswa</th>
                                <th class="px-4 py-3 text-left">Jurusan</th>
                                <th class="px-4 py-3 text-left">NIM</th>
                                <th class="px-4 py-3 text-left">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-emerald-50">
                            @foreach ($students as $student)
                                <tr class="hover:bg-emerald-50/40">
                                    <td class="px-4 py-3">
                                        <input type="checkbox" name="student_ids[]" value="{{ $student->id }}" class="row-checkbox rounded border-emerald-300" checked>
                                    </td>
                                    <td class="px-4 py-3 text-emerald-900 font-medium">{{ $student->name }}</td>
                                    <td class="px-4 py-3 text-emerald-600">{{ $student->classroom ?? '—' }}</td>
                                    <td class="px-4 py-3 text-emerald-600">{{ $student->nisn ?? '—' }}</td>
                                    <td class="px-4 py-3">
                                        <select name="status[{{ $student->id }}]" class="rounded-lg border border-emerald-200 px-3 py-2 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-transparent">
                                            @foreach (['hadir' => 'Hadir', 'izin' => 'Izin', 'sakit' => 'Sakit', 'alpa' => 'Alpa'] as $value => $label)
                                                <option value="{{ $value }}" {{ old("status.{$student->id}", 'hadir') === $value ? 'selected' : '' }}>{{ $label }}</option>
                                            @endforeach
                                        </select>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="flex items-center justify-end gap-2">
                    <a href="{{ route('guru.attendances.index') }}" class="px-4 py-2 rounded-lg border border-emerald-200 text-emerald-600 hover:bg-emerald-50">Batal</a>
                    <button type="submit" class="px-4 py-2 rounded-lg bg-emerald-600 text-white font-medium shadow hover:bg-emerald-700">Simpan</button>
                </div>
            </form>

            <script>
                (function(){
                    const selectAll = document.getElementById('select-all');
                    if (!selectAll) return;
                    const checkboxes = document.querySelectorAll('.row-checkbox');
                    selectAll.addEventListener('change', function(){
                        checkboxes.forEach(cb => { cb.checked = selectAll.checked; });
                    });
                })();
            </script>
        @elseif (!empty($subjectId) && $students->count() === 0)
            <p class="text-sm text-emerald-500">Tidak ada mahasiswa untuk kombinasi mata kuliah dan jurusan yang dipilih.</p>
        @else
            <p class="text-sm text-emerald-500">Pilih mata kuliah dan jurusan terlebih dahulu untuk menampilkan daftar mahasiswa.</p>
        @endif
    </div>
@endsection

