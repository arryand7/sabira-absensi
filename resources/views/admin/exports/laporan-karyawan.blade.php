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
        @foreach($laporan as $row)
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
