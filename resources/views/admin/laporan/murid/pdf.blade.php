<!DOCTYPE html>
<html>
<head>
    <style>
        body { font-family: sans-serif; font-size: 12px; }
        h2 { text-align: center; margin-bottom: 20px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        th, td { border: 1px solid #000; padding: 6px; text-align: center; }
    </style>
</head>
<body>
    <h2>Laporan Kehadiran Siswa</h2>

    <p><strong>Nama:</strong> {{ $student->nama_lengkap }}</p>
    <p><strong>NIS:</strong> {{ $student->nis }}</p>
    <p><strong>Tahun Ajaran:</strong> {{ $tahun ?? '-' }}</p>

    @foreach ($rekap as $jenis => $mapels)
        <h3>Jenis: {{ ucfirst($jenis) }}</h3>
        <table>
            <thead>
                <tr>
                    <th>Mata Pelajaran</th>
                    <th>H</th>
                    <th>I</th>
                    <th>S</th>
                    <th>A</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($mapels as $mapel => $data)
                    <tr>
                        <td>{{ $mapel }}</td>
                        <td>{{ $data['H'] }}</td>
                        <td>{{ $data['I'] }}</td>
                        <td>{{ $data['S'] }}</td>
                        <td>{{ $data['A'] }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endforeach

    @if (empty($rekap))
        <p>Tidak ada data absensi untuk siswa ini.</p>
    @endif
</body>
</html>
