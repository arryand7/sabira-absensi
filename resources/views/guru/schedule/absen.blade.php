<x-app-layout>
    <div class="p-4 max-w-5xl mx-auto">
        <h2 class="text-xl font-bold mb-4 text-[#292D22]">Absensi Siswa - {{ $classGroup->nama_kelas }}</h2>

        <form action="{{ route('guru.schedule.absen.submit', $classGroup->id) }}" method="POST">
            @csrf

            {{-- Info Jadwal --}}
            <div class="grid grid-cols-2 md:grid-cols-3 gap-4 mb-6 bg-[#EFF0ED] p-4 rounded-2xl shadow">
                <div>
                    <label class="font-semibold text-[#535A44]">Mata Pelajaran</label>
                    <div class="font-bold text-[#1C1E17]">{{ $schedule->subject->nama_mapel }}</div>
                    <input type="hidden" name="mata_pelajaran" value="{{ $schedule->subject->nama_mapel }}">
                </div>
                <div>
                    <label class="font-semibold text-[#535A44]">Kode</label>
                    <div class="font-bold text-[#1C1E17]">{{ $schedule->subject->kode_mapel }}</div>
                    <input type="hidden" name="kode_mapel" value="{{ $schedule->subject->kode_mapel }}">
                </div>
                <div>
                    <label class="font-semibold text-[#535A44]">Tanggal</label>
                    <div class="font-bold text-[#1C1E17]">{{ now()->format('Y-m-d') }}</div>
                    <input type="hidden" name="tanggal" value="{{ now()->format('Y-m-d') }}">
                </div>
                <div>
                    <label class="font-semibold text-[#535A44]">Pertemuan Ke-</label>
                    <input type="number" name="pertemuan" value="{{ now()->weekOfMonth }}" min="1" required
                           class="w-full border border-[#D6D8D2] p-2 rounded bg-white text-[#1C1E17]">
                </div>
                <div>
                    <label class="font-semibold text-[#535A44]">Jam Mulai</label>
                    <div class="font-bold text-[#1C1E17]">{{ $schedule->jam_mulai }}</div>
                    <input type="hidden" name="jam_mulai" value="{{ $schedule->jam_mulai }}">
                </div>
                <div>
                    <label class="font-semibold text-[#535A44]">Jam Selesai</label>
                    <div class="font-bold text-[#1C1E17]">{{ $schedule->jam_selesai }}</div>
                    <input type="hidden" name="jam_selesai" value="{{ $schedule->jam_selesai }}">
                </div>
                <div class="col-span-full">
                    <label class="font-semibold text-[#535A44]">Materi</label>
                    <textarea name="materi" rows="2" placeholder="Jelaskan pembahasan materi..."
                              class="w-full border border-[#D6D8D2] p-2 rounded bg-white text-[#1C1E17]"></textarea>
                </div>
            </div>

            <input type="hidden" name="schedule_id" value="{{ $schedule->id }}">

            {{-- Search --}}
            <div class="mb-4">
                <input type="search" id="studentSearch" placeholder="Cari nama siswa..."
                       class="w-full p-2 border border-[#D6D8D2] rounded bg-white text-[#1C1E17]" autofocus>
            </div>

            {{-- Tabel Absensi --}}
            <div class="overflow-x-auto bg-[#F7F7F6] rounded-xl p-4 shadow">
                <table id="attendanceTable" class="min-w-full text-sm">
                    <thead>
                        <tr class="bg-[#D6D8D2] text-[#1C1E17] uppercase text-xs">
                            <th class="px-4 py-2 text-left">No</th>
                            <th class="px-4 py-2 text-left">Nama</th>
                            <th class="px-4 py-2 text-left">NIM</th>
                            <th class="px-2 text-center" colspan="4">Kehadiran</th>
                        </tr>
                        <tr class="bg-[#EFF0ED] text-[#535A44] text-xs">
                            <th colspan="3"></th>
                            <th class="px-2 text-center">Hadir</th>
                            <th class="px-2 text-center">Alpa</th>
                            <th class="px-2 text-center">Sakit</th>
                            <th class="px-2 text-center">Izin</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($classGroup->students as $index => $student)
                            <tr class="border-t border-[#D6D8D2] hover:bg-[#EFF0ED] text-[#1C1E17]">
                                <td class="px-4 py-2">{{ $index + 1 }}</td>
                                <td class="px-4 py-2 student-name">{{ $student->nama_lengkap }}</td>
                                <td class="px-4 py-2">{{ $student->nis }}</td>
                                @foreach (['hadir', 'alpa', 'sakit', 'izin'] as $status)
                                    <td class="text-center">
                                        <input
                                            type="radio"
                                            name="attendance[{{ $student->id }}]"
                                            value="{{ $status }}"
                                            required
                                            {{ $status == 'hadir' ? 'checked' : '' }}>
                                    </td>
                                @endforeach
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="mt-6 text-center">
                <button type="submit"
                        class="bg-[#5C644C] hover:bg-[#535A44] text-white px-6 py-2 rounded font-semibold shadow">
                    <i class="bi bi-send me-2"></i> SUBMIT
                </button>
            </div>
        </form>

        {{-- JS Filter --}}
        <script>
            document.addEventListener('DOMContentLoaded', () => {
                const searchInput = document.getElementById('studentSearch');
                const tableBody = document.getElementById('attendanceTable').getElementsByTagName('tbody')[0];

                searchInput.addEventListener('input', function () {
                    const filter = this.value.toLowerCase();
                    const rows = tableBody.getElementsByTagName('tr');

                    for (let row of rows) {
                        const nameCell = row.querySelector('.student-name');
                        if (nameCell) {
                            const name = nameCell.textContent.toLowerCase();
                            row.style.display = name.includes(filter) ? '' : 'none';
                        }
                    }
                });
            });
        </script>
    </div>
</x-app-layout>
