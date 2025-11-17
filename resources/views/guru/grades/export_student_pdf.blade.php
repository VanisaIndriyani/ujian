<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ $title ?? 'Data Nilai Siswa' }}</title>
    <style>
        body { font-family: DejaVu Sans, Arial, sans-serif; font-size: 12px; }
        h1 { font-size: 18px; margin: 0 0 10px; }
        .meta { margin-bottom: 10px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #ddd; padding: 6px; }
        th { background: #f3f7f5; text-transform: uppercase; font-size: 11px; }
        .right { text-align: right; }
    </style>
</head>
<body>
    <h1>{{ $title ?? 'Data Nilai Siswa' }}</h1>
    <div class="meta">
        <strong>Mahasiswa:</strong> {{ $student->name }}<br>
        <strong>Kelas:</strong> {{ $student->classroom ?? '—' }}<br>
        <strong>Mata Kuliah:</strong> {{ $subject }}<br>
        <strong>Bobot:</strong>
        Absen {{ $weights['wAbsen'] ?? 10 }}% | Praktikum {{ $weights['wPraktikum'] ?? 10 }}% | Tugas {{ $weights['wTugas'] ?? 20 }}% | UTS {{ $weights['wUts'] ?? 30 }}% | UAS {{ $weights['wUas'] ?? 30 }}%
    </div>

    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>Nama Lengkap</th>
                <th>Kehadiran</th>
                <th>Nilai UTS</th>
                <th>Nilai UAS</th>
                <th>Nilai Tugas</th>
                <th>Nilai Praktikum</th>
                <th>Rata-rata</th>
                <th>Predikat</th>
                <th>Keterangan</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>1</td>
                <td>{{ $student->name }}</td>
                <td class="right">{{ $components['attendance'] ?? '—' }}</td>
                <td class="right">{{ $components['uts'] ?? '—' }}</td>
                <td class="right">{{ $components['uas'] ?? '—' }}</td>
                <td class="right">{{ $components['tugas'] ?? '—' }}</td>
                <td class="right">{{ $components['praktikum'] ?? '—' }}</td>
                <td class="right">{{ $finalScore ?? '—' }}</td>
                <td>{{ $predikat ?? '—' }}</td>
                <td>{{ $keterangan ?? '—' }}</td>
            </tr>
        </tbody>
    </table>
</body>
</html>