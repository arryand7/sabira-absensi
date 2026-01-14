<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: sans-serif; font-size: 12px; color: #111827; }
        h2 { text-align: center; margin-bottom: 12px; }
        .meta { margin-bottom: 12px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #111827; padding: 6px; text-align: left; }
        th { background-color: #f3f4f6; }
        .summary { margin-bottom: 12px; }
        .summary td { border: none; padding: 2px 0; }
    </style>
</head>
<body>
    <h2>Laporan Pertemuan Guru</h2>

    <div class="meta">
        <div>Periode: {{ $filters['start_date'] }} s.d {{ $filters['end_date'] }}</div>
    </div>

    <table class="summary">
        <tr>
            <td>Total Pertemuan:</td>
            <td>{{ $summary['total_sessions'] }}</td>
            <td>Total Hadir:</td>
            <td>{{ $summary['hadir'] }}</td>
        </tr>
        <tr>
            <td>Total Izin:</td>
            <td>{{ $summary['izin'] }}</td>
            <td>Total Sakit:</td>
            <td>{{ $summary['sakit'] }}</td>
        </tr>
        <tr>
            <td>Total Alpa:</td>
            <td>{{ $summary['alpa'] }}</td>
            <td></td>
            <td></td>
        </tr>
    </table>

    <table>
        <thead>
            <tr>
                <th>Tanggal</th>
                <th>Pertemuan</th>
                <th>Guru</th>
                <th>Mapel</th>
                <th>Kelas</th>
                <th>Jam</th>
                <th>H</th>
                <th>I</th>
                <th>S</th>
                <th>A</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($sessions as $session)
                <tr>
                    <td>{{ \Carbon\Carbon::parse($session->date)->format('d M Y') }}</td>
                    <td>{{ $session->meeting_no ?? '-' }}</td>
                    <td>{{ $session->schedule->user->name ?? '-' }}</td>
                    <td>{{ $session->schedule->subject->nama_mapel ?? '-' }}</td>
                    <td>{{ $session->schedule->classGroup->nama_kelas ?? '-' }}</td>
                    <td>{{ $session->start_time }} - {{ $session->end_time }}</td>
                    <td>{{ $session->hadir_count }}</td>
                    <td>{{ $session->izin_count }}</td>
                    <td>{{ $session->sakit_count }}</td>
                    <td>{{ $session->alpa_count }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
