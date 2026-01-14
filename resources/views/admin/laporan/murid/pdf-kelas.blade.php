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
    <h2>Laporan Absensi Kelas</h2>

    <p><strong>Kelas:</strong> {{ $kelas }}</p>
    <p><strong>Tahun Ajaran:</strong> {{ $tahun }}</p>
    <p><strong>Total Pertemuan:</strong> {{ $totalPertemuan }}</p>

    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>Nama</th>
                <th>NIS</th>
                <th>H</th>
                <th>I</th>
                <th>S</th>
                <th>A</th>
            </tr>
        </thead>
        <tbody>
            @forelse($rows as $index => $row)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $row['nama'] }}</td>
                    <td>{{ $row['nis'] }}</td>
                    <td>{{ $row['H'] }}</td>
                    <td>{{ $row['I'] }}</td>
                    <td>{{ $row['S'] }}</td>
                    <td>{{ $row['A'] }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="7">Tidak ada data absensi untuk kelas ini.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>
