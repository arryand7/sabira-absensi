<x-app-layout>
    <div class="sm:px-6 lg:px-8">
        <x-page-title title="LAPORAN PERTEMUAN GURU" />
    </div>

    <x-slot name="sidebar">
        <x-admin-sidenav />
    </x-slot>

    <div class="mt-6 w-full sm:px-6 lg:px-8 space-y-6">
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4">
            <div class="bg-[#EFF0ED] border border-[#D6D8D2] rounded-xl p-4">
                <p class="text-xs uppercase tracking-wide text-[#8D9382]">Total Pertemuan</p>
                <p class="mt-2 text-2xl font-semibold text-[#1C1E17]">{{ $summary['total_sessions'] }}</p>
            </div>
            <div class="bg-[#ECFDF5] border border-[#A7F3D0] rounded-xl p-4">
                <p class="text-xs uppercase tracking-wide text-[#065F46]">Total Hadir</p>
                <p class="mt-2 text-2xl font-semibold text-[#065F46]">{{ $summary['hadir'] }}</p>
            </div>
            <div class="bg-[#FEF3C7] border border-[#FCD34D] rounded-xl p-4">
                <p class="text-xs uppercase tracking-wide text-[#92400E]">Total Izin</p>
                <p class="mt-2 text-2xl font-semibold text-[#92400E]">{{ $summary['izin'] }}</p>
            </div>
            <div class="bg-[#E0E7FF] border border-[#A5B4FC] rounded-xl p-4">
                <p class="text-xs uppercase tracking-wide text-[#3730A3]">Total Sakit</p>
                <p class="mt-2 text-2xl font-semibold text-[#3730A3]">{{ $summary['sakit'] }}</p>
            </div>
            <div class="bg-[#FEE2E2] border border-[#FCA5A5] rounded-xl p-4">
                <p class="text-xs uppercase tracking-wide text-[#991B1B]">Total Alpa</p>
                <p class="mt-2 text-2xl font-semibold text-[#991B1B]">{{ $summary['alpa'] }}</p>
            </div>
        </div>

        <div class="bg-[#EEF3E9] shadow-md rounded-2xl p-6">
            <form action="{{ route('laporan.pertemuan') }}" method="GET" class="mb-6 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                <div>
                    <label class="block text-sm font-medium text-gray-700">Tahun Ajaran</label>
                    <select name="tahun_ajaran" class="mt-1 w-full rounded-md border-gray-300 shadow-sm focus:ring focus:ring-orange-200">
                        @foreach($academicYears as $year)
                            <option value="{{ $year->id }}" {{ (string) $selectedYear === (string) $year->id ? 'selected' : '' }}>
                                {{ $year->name }}{{ $year->is_active ? ' (Aktif)' : '' }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Guru</label>
                    <select name="guru_id" class="mt-1 w-full rounded-md border-gray-300 shadow-sm focus:ring focus:ring-orange-200">
                        <option value="">Semua</option>
                        @foreach($teachers as $teacher)
                            <option value="{{ $teacher->id }}" {{ request('guru_id') == $teacher->id ? 'selected' : '' }}>
                                {{ $teacher->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Kelas</label>
                    <select name="kelas_id" class="mt-1 w-full rounded-md border-gray-300 shadow-sm focus:ring focus:ring-orange-200">
                        <option value="">Semua</option>
                        @foreach($classGroups as $group)
                            <option value="{{ $group->id }}" {{ request('kelas_id') == $group->id ? 'selected' : '' }}>
                                {{ $group->nama_kelas }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Mata Pelajaran</label>
                    <select name="mapel_id" class="mt-1 w-full rounded-md border-gray-300 shadow-sm focus:ring focus:ring-orange-200">
                        <option value="">Semua</option>
                        @foreach($subjects as $subject)
                            <option value="{{ $subject->id }}" {{ request('mapel_id') == $subject->id ? 'selected' : '' }}>
                                {{ $subject->nama_mapel }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Dari Tanggal</label>
                    <input type="date" name="start_date" value="{{ $startDate }}"
                        class="mt-1 w-full rounded-md border-gray-300 shadow-sm focus:ring focus:ring-orange-200">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Sampai Tanggal</label>
                    <input type="date" name="end_date" value="{{ $endDate }}"
                        class="mt-1 w-full rounded-md border-gray-300 shadow-sm focus:ring focus:ring-orange-200">
                </div>
                <div class="flex items-end gap-2">
                    <button type="submit"
                        class="bg-[#8E412E] text-white px-4 py-2 rounded-md hover:bg-[#BA6F4D] flex items-center gap-2 shadow">
                        <i class="bi bi-funnel-fill"></i> Filter
                    </button>
                    <a href="{{ route('laporan.pertemuan.export.pdf', request()->only('tahun_ajaran', 'guru_id', 'kelas_id', 'mapel_id', 'start_date', 'end_date')) }}"
                        class="bg-green-600 text-white px-4 py-2 rounded-md hover:bg-green-700 flex items-center gap-2 shadow">
                        <i class="bi bi-file-earmark-pdf-fill"></i> Download PDF
                    </a>
                    <a href="{{ route('laporan.pertemuan.export.excel', request()->only('tahun_ajaran', 'guru_id', 'kelas_id', 'mapel_id', 'start_date', 'end_date')) }}"
                        class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700 flex items-center gap-2 shadow">
                        <i class="bi bi-file-earmark-excel-fill"></i> Download Excel
                    </a>
                    <a href="{{ route('laporan.pertemuan') }}"
                        class="bg-gray-500 text-white px-4 py-2 rounded-md hover:bg-gray-600 flex items-center gap-2 shadow">
                        <i class="bi bi-x-circle-fill"></i> Reset
                    </a>
                </div>
            </form>

            <div class="overflow-x-auto">
                <table id="pertemuanTable" class="w-full text-sm text-left text-[#373C2E]">
                    <thead class="bg-[#8D9382] text-white uppercase text-xs font-semibold">
                        <tr>
                            <th class="px-4 py-3">Tanggal</th>
                            <th class="px-4 py-3">Pertemuan</th>
                            <th class="px-4 py-3">Guru</th>
                            <th class="px-4 py-3">Mapel</th>
                            <th class="px-4 py-3">Kelas</th>
                            <th class="px-4 py-3">Jam</th>
                            <th class="px-4 py-3 text-center">Hadir</th>
                            <th class="px-4 py-3 text-center">Izin</th>
                            <th class="px-4 py-3 text-center">Sakit</th>
                            <th class="px-4 py-3 text-center">Alpa</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#D6D8D2]">
                        @forelse($sessions as $session)
                            <tr class="hover:bg-[#BEC1B7] transition">
                                <td class="px-4 py-3">{{ \Carbon\Carbon::parse($session->date)->format('d M Y') }}</td>
                                <td class="px-4 py-3">{{ $session->meeting_no ?? '-' }}</td>
                                <td class="px-4 py-3">{{ $session->schedule->user->name ?? '-' }}</td>
                                <td class="px-4 py-3">{{ $session->schedule->subject->nama_mapel ?? '-' }}</td>
                                <td class="px-4 py-3">{{ $session->schedule->classGroup->nama_kelas ?? '-' }}</td>
                                <td class="px-4 py-3">{{ $session->start_time }} - {{ $session->end_time }}</td>
                                <td class="px-4 py-3 text-center">{{ $session->hadir_count }}</td>
                                <td class="px-4 py-3 text-center">{{ $session->izin_count }}</td>
                                <td class="px-4 py-3 text-center">{{ $session->sakit_count }}</td>
                                <td class="px-4 py-3 text-center">{{ $session->alpa_count }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="10" class="px-4 py-6 text-center text-[#8D9382]">Belum ada data pertemuan.</td>
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
                $('#pertemuanTable').DataTable({
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
                    order: [[0, 'desc']]
                });
            });
        </script>
    @endpush
</x-app-layout>
