<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: sans-serif; font-size: 12px; color: #111827; }
        h2 { text-align: center; margin-bottom: 12px; }
        .meta { margin-bottom: 12px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 16px; }
        th, td { border: 1px solid #111827; padding: 6px; text-align: left; }
        th { background-color: #f3f4f6; }
        .section-title { margin: 12px 0 6px; font-weight: bold; }
    </style>
</head>
<body>
    <h2>Laporan Absensi Karyawan</h2>

    <div class="meta">
        <div>Periode: {{ $filters['start_date'] }} s.d {{ $filters['end_date'] }}</div>
        <div>Divisi: {{ $filters['divisi_label'] }}</div>
    </div>

    @if(count($laporanKaryawan) > 0)
        <div class="section-title">Karyawan</div>
        <table>
            <thead>
                <tr>
                    <th>Nama</th>
                    <th>Email</th>
                    <th>Divisi</th>
                    <th>Total Hadir</th>
                    <th>Total Absen</th>
                </tr>
            </thead>
            <tbody>
                @foreach($laporanKaryawan as $row)
                    <tr>
                        <td>{{ $row['user']->name }}</td>
                        <td>{{ $row['user']->email }}</td>
                        <td>{{ $row['user']->karyawan->divisi->nama ?? '-' }}</td>
                        <td>{{ $row['hadir'] }}</td>
                        <td>{{ $row['absen'] }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif

    @foreach($laporanGuru as $jenis => $rows)
        @if(count($rows) > 0)
            <div class="section-title">Guru {{ ucfirst($jenis) }}</div>
            <table>
                <thead>
                    <tr>
                        <th>Nama</th>
                        <th>Email</th>
                        <th>Divisi</th>
                        <th>Total Hadir</th>
                        <th>Total Absen</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($rows as $row)
                        <tr>
                            <td>{{ $row['user']->name }}</td>
                            <td>{{ $row['user']->email }}</td>
                            <td>Guru {{ ucfirst($jenis) }}</td>
                            <td>{{ $row['hadir'] }}</td>
                            <td>{{ $row['absen'] }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    @endforeach
</body>
</html>
