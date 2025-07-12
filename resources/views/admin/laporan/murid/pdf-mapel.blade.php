<!DOCTYPE html>
<html lang="ar">
<head>
    <meta charset="UTF-8">
    <title>Laporan Absensi - {{ $mapel }}</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Amiri:ital,wght@0,400;0,700;1,400;1,700&display=swap');

        body {
            font-family: "Amiri", serif;
        }

        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #000; padding: 6px; text-align: center; }
        th { background-color: #f0f0f0; }

        .arabic {
            direction: rtl;
            text-align: right;
            font-family: "Amiri", serif;
        }

        .flex-rtl {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .rtl-text {
            direction: rtl;
            text-align: right;
            font-family: "Amiri", serif;
        }
    </style>
</head>
<body>
    <h2>Laporan Absensi</h2>
    {{-- <p><strong>Kelas:</strong> {{ $kelas ?? 'Semua' }}</p> --}}
    <p><strong>Mata Pelajaran:</strong> {{ $mapel ?? 'Semua' }}</p>
    <p><strong>Tahun Ajaran:</strong> {{ $tahun }}</p>
    @php
        $isArabic = preg_match('/[\x{0600}-\x{06FF}]/u', $kelas);
        $kelasFormatted = $isArabic ? "\u{202B}" . $kelas . "\u{202C}" : $kelas;
    @endphp

    <p><strong>Kelas:</strong> <span class="{{ $isArabic ? 'arabic' : '' }}">{{ $kelasFormatted }}</span></p>
    <p><strong>Total Pertemuan:</strong> {{ $totalPertemuan }} pertemuan</p>


    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>Nama</th>
                <th>NIS</th>
                {{-- <th>Kelas</th> --}}
                <th>H</th>
                <th>I</th>
                <th>S</th>
                <th>A</th>
            </tr>
        </thead>
        <tbody>
            @foreach($rekapMapel->values() as $index => $row)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $row['nama'] }}</td>
                    <td>{{ $row['nis'] }}</td>
                    {{-- <td>{{ $row['kelas'] }}</td> --}}
                    <td>{{ $row['H'] }}</td>
                    <td>{{ $row['I'] }}</td>
                    <td>{{ $row['S'] }}</td>
                    <td>{{ $row['A'] }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
