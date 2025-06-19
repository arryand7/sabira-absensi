<x-app-layout>
    <div class="text-center mt-4">
        <h2 class="text-2xl font-semibold text-[#292D22]">Daftar Kegiatan Asrama</h2>
    </div>

    <div class="py-6 max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
        {{-- Tombol tambah kegiatan --}}
        <div class="mb-6">
            <button onclick="document.getElementById('formKegiatan').classList.toggle('hidden')"
                class="inline-flex items-center px-4 py-2 bg-[#5C644C] text-white text-sm font-medium rounded-lg shadow hover:bg-[#4B543F] transition">
                <i class="bi bi-plus-lg mr-2"></i> Tambah Kegiatan
            </button>
        </div>

        {{-- Form input kegiatan --}}
        <div id="formKegiatan" class="hidden mb-6 bg-[#EFF0ED] rounded-xl shadow p-6 space-y-4 border border-[#D6D8D2]">
            <form action="{{ route('asrama.kegiatan.create') }}" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label for="nama" class="block text-sm font-medium text-[#44483B]">Nama Kegiatan</label>
                    <input type="text" name="nama" id="nama"
                        class="mt-1 w-full rounded-lg border-[#BFC2B8] shadow-sm focus:ring-[#C6D2B2] focus:border-[#C6D2B2]" required>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label for="tanggal" class="block text-sm font-medium text-[#44483B]">Tanggal</label>
                        <input type="date" name="tanggal" id="tanggal"
                            class="mt-1 w-full rounded-lg border-[#BFC2B8] shadow-sm focus:ring-[#C6D2B2] focus:border-[#C6D2B2]" required>
                    </div>

                    <div>
                        <label for="jam_mulai" class="block text-sm font-medium text-[#44483B]">Jam Mulai</label>
                        <input type="time" name="jam_mulai" id="jam_mulai"
                            class="mt-1 w-full rounded-lg border-[#BFC2B8] shadow-sm focus:ring-[#C6D2B2] focus:border-[#C6D2B2]" required>
                    </div>

                    <div>
                        <label for="jam_selesai" class="block text-sm font-medium text-[#44483B]">Jam Selesai</label>
                        <input type="time" name="jam_selesai" id="jam_selesai"
                            class="mt-1 w-full rounded-lg border-[#BFC2B8] shadow-sm focus:ring-[#C6D2B2] focus:border-[#C6D2B2]" required>
                    </div>
                </div>

                <div class="text-right">
                    <button type="submit"
                        class="inline-flex items-center px-4 py-2 bg-[#5C644C] text-white text-sm font-medium rounded-lg shadow hover:bg-[#4B543F] transition">
                        <i class="bi bi-check-lg mr-2"></i> Simpan
                    </button>
                </div>
            </form>
        </div>

        {{-- Tabel kegiatan --}}
        <div class="overflow-x-auto bg-white rounded-2xl shadow-md border border-[#D6D8D2] px-6 py-4">
            <table id="kegiatanTable" class="min-w-full divide-y divide-gray-200 text-sm text-left text-[#292D22]">
                <thead class="bg-[#DFE5D7] text-[#292D22] uppercase font-semibold tracking-wide">
                    <tr>
                        <th class="px-6 py-4">Nama Kegiatan</th>
                        <th class="px-6 py-4">Tanggal</th>
                        <th class="px-6 py-4">Jam</th>
                        <th class="px-6 py-4 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#F1F1EF] bg-white">
                    @foreach($kegiatan->sortByDesc('tanggal') as $k)
                        <tr class="hover:bg-[#F5F6F3] transition-colors duration-150">
                            <td class="px-6 py-4 whitespace-nowrap">{{ $k->kegiatanAsrama->nama }}</td>
                            <td class="px-6 py-4 whitespace-nowrap">{{ $k->tanggal }}</td>
                            <td class="px-6 py-4 whitespace-nowrap">{{ $k->jam_mulai }} - {{ $k->jam_selesai }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-center space-x-2">
                                @if($k->sudah_dinilai)
                                    <span class="inline-flex items-center bg-gray-400 text-white px-3 py-1.5 rounded-lg text-xs font-medium shadow">
                                        <i class="bi bi-clipboard-check mr-1"></i> Sudah Absen
                                    </span>
                                @else
                                    <a href="{{ route('asrama.kegiatan.absen', $k->id) }}"
                                        class="inline-flex items-center bg-[#4F684B] hover:bg-[#3E563A] text-white px-3 py-1.5 rounded-lg text-xs font-medium shadow transition">
                                        <i class="bi bi-clipboard-check mr-1"></i> Absen
                                    </a>
                                @endif
                                <a href="{{ route('asrama.kegiatan.history', $k->id) }}"
                                    class="inline-flex items-center bg-[#8B8E7C] hover:bg-[#757867] text-white px-3 py-1.5 rounded-lg text-xs font-medium shadow transition">
                                    <i class="bi bi-clock-history mr-1"></i> History
                                </a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

    </div>

    {{-- DataTables CDN --}}
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            $('#kegiatanTable').DataTable({
                order: [[1, 'desc']], // Order by tanggal DESC
                language: {
                    search: "Cari:",
                    lengthMenu: "Tampilkan _MENU_ entri",
                    info: "Menampilkan _START_ sampai _END_ dari _TOTAL_ entri",
                    paginate: {
                        next: "Berikutnya",
                        previous: "Sebelumnya"
                    },
                    zeroRecords: "Tidak ditemukan data yang cocok",
                    infoEmpty: "Tidak ada data tersedia",
                    infoFiltered: "(difilter dari total _MAX_ entri)"
                }
            });
        });
    </script>
</x-app-layout>
