<x-app-layout>
    
    <div class="py-4 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

        <h2 class="font-semibold text-2xl text-[#292D22] mb-6">
            Jadwal Mengajar
        </h2>

        <div class="bg-[#EEF3E9] shadow-md rounded-2xl p-6 space-y-4">

            {{-- Tombol Kembali --}}
            <div class="flex justify-end">
                <a href="{{ route('dashboard') }}"
                   class="bg-[#D6D8D2] hover:bg-[#BEC1B7] text-[#1C1E17] px-4 py-2 rounded-md text-sm flex items-center gap-2 shadow transition">
                    
                   <i class="bi bi-arrow-left-circle"></i> Kembali
                </a>
            </div>

            {{-- Tombol Tambah --}}
            <div class="mb-4">
                <a href="{{ route('guru.schedule.create', ['guru_id' => $guru->id]) }}"
                   class="inline-flex items-center gap-1 bg-[#8E412E] hover:bg-[#BA6F4D] text-white font-medium px-4 py-2 rounded shadow">
                    <i class="bi bi-plus-circle-fill"></i> Tambah Jadwal
                </a>
            </div>

            {{-- Table --}}
            <div class="overflow-x-auto">
                <table id="jadwalTable" class="w-full text-sm text-left text-[#373C2E]">
                    <thead class="bg-[#8D9382] text-white uppercase text-xs font-semibold">
                        <tr>
                            <th class="px-4 py-3">Mata Pelajaran</th>
                            <th class="px-4 py-3">Kode</th>
                            <th class="px-4 py-3">Hari</th>
                            <th class="px-4 py-3">Mulai</th>
                            <th class="px-4 py-3">Selesai</th>
                            <th class="px-4 py-3">Kelas</th>
                            <th class="px-4 py-3 text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#D6D8D2] bg-white">
                        @forelse ($schedules as $schedule)
                            <tr class="hover:bg-[#EFF0ED] transition">
                                <td class="px-4 py-3">{{ $schedule->subject->nama_mapel }}</td>
                                <td class="px-4 py-3">{{ $schedule->subject->kode_mapel }}</td>
                                <td class="px-4 py-3">{{ $schedule->hari }}</td>
                                <td class="px-4 py-3">{{ $schedule->jam_mulai }}</td>
                                <td class="px-4 py-3">{{ $schedule->jam_selesai }}</td>
                                <td class="px-4 py-3">{{ $schedule->classGroup->nama_kelas }}</td>
                                <td class="px-4 py-3 text-center">
                                    <a href="{{ route('guru.schedule.absen', ['schedule' => $schedule->id]) }}"
                                       class="inline-flex items-center gap-1 px-3 py-1 bg-[#5C644C] text-white rounded-md text-xs hover:bg-[#535A44] transition shadow">
                                        <i class="bi bi-clipboard-check"></i> Absen
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center px-4 py-4 text-[#8D9382]">
                                    <i class="bi bi-info-circle me-1"></i> Belum ada jadwal mengajar.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                $('#jadwalTable').DataTable({
                    language: {
                        search: "Cari:",
                        lengthMenu: "Tampilkan _MENU_ entri",
                        info: "Menampilkan _START_ sampai _END_ dari _TOTAL_ entri",
                        paginate: {
                            first: "Pertama",
                            last: "Terakhir",
                            next: "›",
                            previous: "‹"
                        },
                        zeroRecords: "Tidak ditemukan data yang sesuai",
                    },
                    responsive: true,
                    pageLength: 10,
                    ordering: true,
                    order: [[0, 'asc']],
                });
            });
        </script>
    @endpush
</x-app-layout>
