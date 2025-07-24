<x-user-layout>
    <div class="py-6 max-w-4xl mx-auto sm:px-6 lg:px-8">
        <h2 class="text-xl font-bold text-center text-[#292D22] mb-4">
            History Absensi Kegiatan
        </h2>

        <div class="bg-[#EFF0ED] p-6 rounded-xl shadow-md border border-[#D6D8D2]">
            <div class="flex justify-between items-center mb-4">
                <div>
                    <div class="text-sm text-[#44483B]">
                        <span class="font-semibold">Kegiatan:</span> {{ $kegiatan->kegiatanAsrama->nama }}
                    </div>
                    <div class="text-sm text-[#44483B]">
                        <span class="font-semibold">Tanggal:</span> {{ $kegiatan->tanggal }}
                    </div>
                </div>
                <a href="{{ route('asrama.kegiatan') }}"
                    class="inline-flex items-center gap-2 px-3 py-2 bg-[#5C644C] hover:bg-[#3E563A] text-white rounded-md text-sm shadow transition">
                    <i class="bi bi-arrow-left-circle"></i> Kembali
                </a>
            </div>

            <table id="absensiTable" class="min-w-full divide-y divide-[#D6D8D2] text-sm">
                <thead class="bg-[#DDE3D3] text-[#292D22] uppercase font-semibold">
                    <tr>
                        <th class="px-4 py-3 text-left">NIS</th>
                        <th class="px-4 py-3 text-left">Nama Siswa</th>
                        <th class="px-4 py-3 text-left">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#E7EAE0] text-[#292D22] bg-white">
                    @foreach(\App\Models\Student::orderBy('nama_lengkap')->get() as $siswa)
                        @php
                            $absenSiswa = $absensi->firstWhere('student_id', $siswa->id);
                            $status = $absenSiswa->status ?? 'alpa';
                        @endphp
                        <tr>
                            <td class="px-4 py-3">{{ $siswa->nis }}</td>
                            <td class="px-4 py-3">{{ $siswa->nama_lengkap }}</td>
                            <td class="px-4 py-3 capitalize font-semibold {{ $status === 'hadir' ? 'text-green-600' : 'text-red-600' }}">
                                {{ $status }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            $('#absensiTable').DataTable({
                language: {
                    search: "Cari:",
                    lengthMenu: "Tampilkan _MENU_ siswa",
                    info: "Menampilkan _START_ sampai _END_ dari _TOTAL_ siswa",
                    paginate: {
                        next: "Berikutnya",
                        previous: "Sebelumnya"
                    },
                    zeroRecords: "Tidak ditemukan data absensi",
                    infoEmpty: "Tidak ada data tersedia",
                    infoFiltered: "(difilter dari total _MAX_ siswa)"
                }
            });
        });
    </script>
</x-user-layout>
