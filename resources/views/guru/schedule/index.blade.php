<x-user-layout>
    <div class="py-4 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

        <h2 class="font-semibold text-2xl text-[#292D22] mb-6">
            Jadwal Mengajar
        </h2>

        <div class="bg-[#EEF3E9] shadow-md rounded-2xl p-6 space-y-4">

            {{-- Tombol Kembali --}}
            <div class="flex justify-end">
                <a href="{{ route('dashboard') }}"
                   class="bg-[#8E412E] text-white px-4 py-2 rounded-md text-sm sm:text-base hover:bg-[#7A3827] transition">
                ← Kembali
                </a>
            </div>

            {{-- Tombol Tambah --}}
            <div class="mb-4">
                <a href="{{ route('guru.schedule.create', ['guru_id' => $guru->id]) }}"
                   class="inline-flex items-center gap-1 bg-[#8E412E] hover:bg-[#BA6F4D] text-white font-medium px-4 py-2 rounded shadow">
                    <i class="bi bi-plus-circle-fill"></i> Tambah Jadwal
                </a>
            </div>

            {{-- Table View (Desktop) --}}
            <div class="overflow-x-auto hidden md:block">
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
                        @if ($schedules->count() > 0)
                            @foreach ($schedules as $schedule)
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

                                        <a href="{{ route('guru.schedule.edit', ['schedule' => $schedule->id]) }}"
                                           class="inline-flex items-center gap-1 px-3 py-1 bg-yellow-600 text-white text-xs rounded hover:bg-yellow-700 shadow">
                                            <i class="bi bi-pencil-fill"></i> Edit
                                        </a>

                                        <form action="{{ route('guru.schedule.destroy', ['schedule' => $schedule->id]) }}"
                                              method="POST" class="inline delete-form">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit"
                                                    class="inline-flex items-center gap-1 px-3 py-1 bg-red-600 text-white text-xs rounded hover:bg-red-700 shadow">
                                                <i class="bi bi-trash-fill"></i> Hapus
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        @else
                            <tr>
                                <td colspan="7" class="text-center px-4 py-4 text-[#8D9382]">
                                    <i class="bi bi-info-circle me-1"></i> Belum ada jadwal mengajar.
                                </td>
                            </tr>
                        @endif
                    </tbody>
                </table>
            </div>

            {{-- Mobile View with Search and Pagination --}}
            <div x-data="{
                    page: 1,
                    perPage: 5,
                    search: '',
                    filteredSchedules: @js(
                        $schedules->map(fn($s) => [
                            'id' => $s->id,
                            'hari' => $s->hari,
                            'jam_mulai' => $s->jam_mulai,
                            'jam_selesai' => $s->jam_selesai,
                            'subject' => [
                                'nama_mapel' => $s->subject->nama_mapel,
                                'kode_mapel' => $s->subject->kode_mapel,
                            ],
                            'class_group' => [
                                'nama_kelas' => $s->classGroup->nama_kelas,
                            ],
                            'absen_url' => route('guru.schedule.absen', ['schedule' => $s->id])
                        ])
                    ),
                    get totalPages() {
                        return Math.ceil(this.filtered.length / this.perPage);
                    },
                    get filtered() {
                        if (this.search === '') {
                            return this.filteredSchedules;
                        }
                        return this.filteredSchedules.filter(item => {
                            const mapel = item.subject.nama_mapel?.toLowerCase() || '';
                            const kode = item.subject.kode_mapel?.toLowerCase() || '';
                            const hari = item.hari?.toLowerCase() || '';
                            const kelas = item.class_group.nama_kelas?.toLowerCase() || '';
                            return mapel.includes(this.search.toLowerCase()) ||
                                kode.includes(this.search.toLowerCase()) ||
                                hari.includes(this.search.toLowerCase()) ||
                                kelas.includes(this.search.toLowerCase());
                        });
                    },
                    get paginated() {
                        const start = (this.page - 1) * this.perPage;
                        return this.filtered.slice(start, start + this.perPage);
                    }
                }" class="block md:hidden space-y-4">

                {{-- Search --}}
                <div class="mb-4">
                    <input type="text" x-model="search" placeholder="Cari jadwal..."
                        class="w-full border border-[#8D9382] rounded-md px-3 py-2 text-sm text-[#292D22] bg-white shadow-sm focus:ring focus:ring-[#5C644C] focus:outline-none transition" />
                </div>

                {{-- Card Items --}}
                <template x-if="paginated.length > 0">
                    <template x-for="(schedule, i) in paginated" :key="i">
                        <div class="bg-white rounded-xl shadow border border-[#D6D8D2] p-4 space-y-1">
                            <div class="text-sm text-[#292D22]"><span class="font-semibold">Mata Pelajaran:</span> <span x-text="schedule.subject.nama_mapel"></span></div>
                            <div class="text-sm text-[#292D22]"><span class="font-semibold">Kode:</span> <span x-text="schedule.subject.kode_mapel"></span></div>
                            <div class="text-sm text-[#292D22]"><span class="font-semibold">Hari:</span> <span x-text="schedule.hari"></span></div>
                            <div class="text-sm text-[#292D22]"><span class="font-semibold">Jam:</span> <span x-text="schedule.jam_mulai"></span> - <span x-text="schedule.jam_selesai"></span></div>
                            <div class="text-sm text-[#292D22]"><span class="font-semibold">Kelas:</span> <span x-text="schedule.class_group.nama_kelas"></span></div>
                            <div class="pt-2">
                                <a :href="schedule.absen_url"
                                class="inline-flex items-center gap-1 px-3 py-1 bg-[#5C644C] text-white rounded-md text-xs hover:bg-[#535A44] transition shadow">
                                    <i class="bi bi-clipboard-check"></i> Absen
                                </a>
                            </div>
                        </div>
                    </template>
                </template>

                {{-- Empty State --}}
                <div x-show="filtered.length === 0" class="text-center text-[#8D9382]">
                    <i class="bi bi-info-circle me-1"></i> Tidak ditemukan jadwal yang sesuai.
                </div>

                {{-- Pagination Controls --}}
                <template x-if="filtered.length > perPage">
                    <div class="flex justify-center items-center gap-4 pt-4">
                        <button @click="page--" :disabled="page === 1"
                            class="px-3 py-1 rounded bg-[#D6D8D2] text-[#1C1E17] text-sm hover:bg-[#BEC1B7] disabled:opacity-50 disabled:cursor-not-allowed">
                            ‹ Sebelumnya
                        </button>
                        <span class="text-sm text-[#292D22]">Halaman <span x-text="page"></span> / <span x-text="totalPages"></span></span>
                        <button @click="page++" :disabled="page >= totalPages"
                            class="px-3 py-1 rounded bg-[#D6D8D2] text-[#1C1E17] text-sm hover:bg-[#BEC1B7] disabled:opacity-50 disabled:cursor-not-allowed">
                            Berikutnya ›
                        </button>
                    </div>
                </template>
            </div>


        </div>
    </div>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            @if ($schedules->count() > 0)
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
            @endif
        });
    </script>
</x-user-layout>
