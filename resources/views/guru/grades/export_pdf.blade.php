<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ $title ?? 'Ringkasan Nilai' }}</title>
    <style>
        body { font-family: DejaVu Sans, Arial, sans-serif; font-size: 12px; }
        h1 { font-size: 18px; margin: 0 0 10px; }
        .meta { margin-bottom: 10px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #ddd; padding: 6px; }
        th { background: #f3f7f5; text-transform: uppercase; font-size: 11px; }
    </style>
}</head>
<body>
    <h1>{{ $title ?? 'Ringkasan Nilai' }}</h1>
    <div class="meta">
        <strong>Filter:</strong>
        Mata Kuliah: {{ $filters['subject_id'] ? $filters['subject_id'] : 'Semua' }} |
        Kelas: {{ $filters['classroom'] ? $filters['classroom'] : 'Semua' }} |
        Cari: {{ $filters['q'] ? $filters['q'] : '—' }}
        <br>
        <strong>Bobot:</strong>
        UTS {{ $filters['w_uts'] ?? 30 }}% | UAS {{ $filters['w_uas'] ?? 30 }}% | Tugas {{ $filters['w_tugas'] ?? 20 }}% | Praktikum {{ $filters['w_praktikum'] ?? 20 }}%
    </div>

    <table>
        <thead>
            <tr>
                <th>Mahasiswa</th>
                <th>Kelas</th>
                <th>Mata Kuliah</th>
                <th>Nilai UTS</th>
                <th>Nilai UAS</th>
                <th>Nilai Tugas</th>
                <th>Nilai Praktikum</th>
                <th>Rata-rata</th>
                <th>Predikat</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($rows as $row)
                <tr>
                    <td>{{ $row['Mahasiswa'] }}</td>
                    <td>{{ $row['Kelas'] }}</td>
                    <td>{{ $row['Mata Kuliah'] }}</td>
                    <td>{{ $row['Nilai UTS'] }}</td>
                    <td>{{ $row['Nilai UAS'] }}</td>
                    <td>{{ $row['Nilai Tugas'] }}</td>
                    <td>{{ $row['Nilai Praktikum'] }}</td>
                    <td>{{ $row['Rata-rata'] }}</td>
                    <td>{{ $row['Predikat'] }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="9" style="text-align:center;">Tidak ada data untuk filter ini.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>