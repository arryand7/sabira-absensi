<x-app-layout>
    <x-slot name="sidebar">
        <x-admin-sidenav />
    </x-slot>

    <div class="mt-6 w-full sm:px-6 lg:px-8 space-y-6">
        <div class="bg-[#EEF3E9] shadow-md rounded-2xl p-6">
            {{-- Filter --}}
            <form method="GET" id="filterForm" class="mb-6 flex flex-wrap items-end gap-4">
                <div>
                    <label for="kelas" class="block text-sm font-medium text-gray-700">Kelas</label>
                    <select name="kelas" id="kelas" class="w-full rounded-md border-gray-300 shadow-sm focus:ring focus:ring-orange-200">
                        <option value="">Semua</option>
                        @foreach($kelasList as $kelas)
                            <option value="{{ $kelas }}" {{ request('kelas') == $kelas ? 'selected' : '' }}>
                                {{ $kelas }}
                            </option>
                        @endforeach
                    </select>
                </div>
                {{-- <div>
                    <label class="block text-sm font-medium text-gray-700">Tahun Ajaran Aktif</label>
                    <div class="text-sm text-gray-900 mt-1">
                        {{ $activeYear?->name ?? '-' }}
                    </div>
                </div> --}}

                {{-- <div class="flex gap-2 mt-1">
                    <button type="submit"
                            class="bg-[#8E412E] text-white px-4 py-2 rounded-md hover:bg-[#BA6F4D] flex items-center gap-2 shadow">
                        <i class="bi bi-funnel-fill"></i> Filter
                    </button>
                </div> --}}
            </form>

            {{-- Table --}}
            <div class="overflow-x-auto">
                <table id="laporanTable" class="w-full text-sm text-left text-[#373C2E]">
                    <thead class="bg-[#8D9382] text-white uppercase text-xs font-semibold">
                        <tr>
                            <th class="px-4 py-3">Nama</th>
                            <th class="px-4 py-3">NIS</th>
                            <th class="px-4 py-3">Kelas</th>
                            <th class="px-4 py-3 text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#D6D8D2]">
                        @if ($students->count() > 0)
                            @foreach($students as $student)
                                <tr class="hover:bg-[#BEC1B7] transition">
                                    <td class="px-4 py-3">{{ $student->nama_lengkap }}</td>
                                    <td class="px-4 py-3">{{ $student->nis }}</td>
                                    <td class="px-4 py-3">{{ $student->kelas }}</td>
                                    <td class="px-4 py-3 text-center">
                                        <a href="{{ route('laporan.murid.download', ['student' => $student->id]) }}"
                                        class="inline-flex items-center gap-1 px-3 py-1 bg-green-600 text-white rounded-md text-xs hover:bg-green-700 shadow">
                                            <i class="bi bi-download"></i> Download
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        @else
                            <tr>
                                <td colspan="4" class="text-center py-6 text-slate-500 italic">
                                    Tidak ada data murid.
                                </td>
                            </tr>
                        @endif
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            $(document).ready(function () {
                $('#kelas').on('change', function () {
                    $('#filterForm').submit();
                });
                
                @if ($students->count() > 0)
                    $('#laporanTable').DataTable({
                        responsive: true,
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
                        pageLength: 10,
                        ordering: true,
                        order: [[0, 'asc']],
                    });
                @endif
            });
        </script>
    @endpush
</x-app-layout>
