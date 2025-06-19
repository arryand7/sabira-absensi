<x-app-layout>
    <h2 class="font-semibold text-xl text-[#292D22]">
        History Absensi Kegiatan: {{ $kegiatan->kegiatanAsrama->nama }} - {{ $kegiatan->tanggal }}
    </h2>

    <div class="py-6 max-w-4xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-[#EFF0ED] p-6 rounded-xl shadow-md border border-[#D6D8D2]">
            <table id="absensiTable" class="min-w-full divide-y divide-[#D6D8D2] text-sm">
                <thead class="bg-[#DDE3D3] text-[#292D22] uppercase font-semibold">
                    <tr>
                        <th class="px-4 py-3 text-left">NIS</th>
                        <th class="px-4 py-3 text-left">Nama Siswa</th>
                        <th class="px-4 py-3 text-left">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#E7EAE0] text-[#292D22] bg-white">
                    @forelse($absensi as $a)
                        <tr>
                            <td class="px-4 py-3">{{ $a->student->nis }}</td>
                            <td class="px-4 py-3">{{ $a->student->nama_lengkap }}</td>
                            <td class="px-4 py-3 capitalize font-semibold {{ $a->status === 'hadir' ? 'text-green-600' : 'text-red-600' }}">
                                {{ $a->status }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="px-4 py-4 text-center text-gray-500">Belum ada absensi.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>

            <div class="mt-6 text-center">
                <a href="{{ route('asrama.kegiatan') }}"
                   class="inline-flex items-center text-[#5C644C] hover:text-[#3E4434] font-medium text-sm">
                    <i class="bi bi-arrow-left mr-1"></i> Kembali ke daftar kegiatan
                </a>
            </div>
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
</x-app-layout>
