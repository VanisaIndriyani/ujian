<style>
    body { font-family: 'Segoe UI', sans-serif; font-size: 12px; color: #1f2937; }
    table { width: 100%; border-collapse: collapse; margin-top: 16px; }
    th, td { border: 1px solid #d1fae5; padding: 8px; text-align: left; }
    th { background: #d1fae5; color: #047857; text-transform: uppercase; font-size: 11px; }
    h1 { font-size: 20px; color: #047857; margin-bottom: 8px; }
    p { margin: 0; color: #059669; }
</style>

<h1>{{ $title }}</h1>
<p>Dibuat pada: {{ now()->format('d M Y H:i') }}</p>

<table>
    <thead>
        <tr>
            <th>Mahasiswa</th>
            <th>Kelas</th>
<th>Mata Kuliah</th>
            <th>Semester</th>
            <th>Nilai</th>
            <th>Catatan</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($records as $record)
            <tr>
                <td>{{ $record->student?->name ?? '-' }}</td>
                <td>{{ $record->student?->classroom ?? '-' }}</td>
                <td>{{ $record->subject?->name ?? '-' }}</td>
                <td>{{ $record->semester }}</td>
                <td>{{ number_format($record->score, 2) }}</td>
                <td>{{ $record->notes ?? '-' }}</td>
            </tr>
        @endforeach
    </tbody>
</table>

