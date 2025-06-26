<x-app-layout>
    <h2 class="font-semibold text-xl text-[#292D22]">
        {{ __('Laporan Absensi Karyawan') }}
    </h2>

    <x-slot name="sidebar">
        <x-admin-sidenav />
    </x-slot>

    <div class="mt-6 w-full sm:px-6 lg:px-8 space-y-6">
        <div class="bg-[#EEF3E9] shadow-md rounded-2xl p-6">
            {{-- Filter --}}
            <form action="{{ route('laporan.karyawan') }}" method="GET" id="filterForm" class="mb-6 flex flex-wrap items-end gap-4">
                {{-- Divisi --}}
                <div>
                    <label for="divisi" class="block text-sm font-medium text-gray-700">Divisi</label>
                    <select name="divisi" id="divisi" class="w-full rounded-md border-gray-300 shadow-sm focus:ring focus:ring-orange-200">
                        <option value="">Semua</option>
                        @foreach($divisis as $d)
                            <option value="{{ $d->nama }}" {{ request('divisi') == $d->nama ? 'selected' : '' }}>
                                {{ $d->nama }}
                            </option>
                        @endforeach
                    </select>
                </div>
                {{-- Jenis Guru --}}
                <div>
                    <label for="jenis_guru" class="block text-sm font-medium text-gray-700">Jenis Guru</label>
                    <select name="jenis_guru" id="jenis_guru" class="w-full rounded-md border-gray-300 shadow-sm focus:ring focus:ring-orange-200">
                        <option value="">Semua</option>
                        <option value="formal" {{ request('jenis_guru') == 'formal' ? 'selected' : '' }}>Formal</option>
                        <option value="muadalah" {{ request('jenis_guru') == 'muadalah' ? 'selected' : '' }}>Muadalah</option>
                    </select>
                </div>

                {{-- Rentang Tanggal --}}
                <div>
                    <label for="start_date" class="block text-sm font-medium text-gray-700">Dari Tanggal</label>
                    <input type="date" id="start_date" name="start_date" value="{{ request('start_date') }}"
                           class="w-full rounded-md border-gray-300 shadow-sm focus:ring focus:ring-orange-200">
                </div>

                <div>
                    <label for="end_date" class="block text-sm font-medium text-gray-700">Sampai Tanggal</label>
                    <input type="date" id="end_date" name="end_date" value="{{ request('end_date') }}"
                           class="w-full rounded-md border-gray-300 shadow-sm focus:ring focus:ring-orange-200">
                </div>

                {{-- Tombol --}}
                <div class="flex gap-2 mt-1">
                    <button type="submit"
                            class="bg-[#8E412E] text-white px-4 py-2 rounded-md hover:bg-[#BA6F4D] flex items-center gap-2 shadow">
                        <i class="bi bi-funnel-fill"></i> Filter
                    </button>

                    <a href="{{ route('laporan.karyawan.export', [
                        'divisi' => request('divisi'),
                        'jenis_guru' => request('jenis_guru'),
                        'start_date' => request('start_date'),
                        'end_date' => request('end_date'),
                    ]) }}"
                    class="bg-green-600 text-white px-4 py-2 rounded-md hover:bg-green-700 flex items-center gap-2 shadow">
                        <i class="bi bi-file-earmark-excel-fill"></i> Download Excel
                    </a>

                    <a href="{{ route('laporan.karyawan') }}"
                    class="bg-gray-500 text-white px-4 py-2 rounded-md hover:bg-gray-600 flex items-center gap-2 shadow">
                        <i class="bi bi-x-circle-fill"></i> Reset Filter
                    </a>
                </div>
            </form>

            {{-- Table --}}
            <div class="overflow-x-auto">
                <table id="laporanTable" class="w-full text-sm text-left text-[#373C2E]">
                    <thead class="bg-[#8D9382] text-white uppercase text-xs font-semibold">
                        <tr>
                            <th class="px-4 py-3">Nama</th>
                            <th class="px-4 py-3">Email</th>
                            <th class="px-4 py-3">Divisi</th>
                            <th class="px-4 py-3 text-center">Total Hadir</th>
                            <th class="px-4 py-3 text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#D6D8D2]">
                        {{-- Karyawan --}}
                        @foreach($laporanKaryawan as $row)
                            <tr class="hover:bg-[#BEC1B7] transition">
                                <td class="px-4 py-3">{{ $row['user']->name }}</td>
                                <td class="px-4 py-3">{{ $row['user']->email }}</td>
                                <td class="px-4 py-3">{{ $row['user']->karyawan->divisi->nama ?? '-' }}</td>
                                <td class="px-4 py-3 text-center">{{ $row['hadir'] }}</td>
                                <td class="px-4 py-3 text-center">
                                    <a href="{{ route('laporan.karyawan.detail', $row['user']->id) }}"
                                        class="inline-flex items-center gap-1 px-3 py-1 bg-[#8E412E] text-white rounded-md text-xs hover:bg-[#BA6F4D] shadow">
                                        <i class="bi bi-eye-fill"></i> Lihat
                                    </a>
                                </td>
                            </tr>
                        @endforeach

                        {{-- Guru --}}
                        @foreach($laporanGuru as $jenis => $rows)
                            @if(count($rows) > 0)
                                {{-- Baris kategori, ditandai pakai class dan data-ignore agar di-skip --}}
                                <tr class="bg-[#D6D8D2] text-[#292D22] font-semibold" data-ignore="true">
                                    <td colspan="5" class="px-4 py-2 uppercase">Guru {{ ucfirst($jenis) }}</td>
                                </tr>
                            @endif
                            @foreach($rows as $row)
                                <tr class="hover:bg-[#BEC1B7] transition">
                                    <td class="px-4 py-3">{{ $row['user']->name }}</td>
                                    <td class="px-4 py-3">{{ $row['user']->email }}</td>
                                    <td class="px-4 py-3">Guru {{ ucfirst($jenis) }}</td>
                                    <td class="px-4 py-3 text-center">{{ $row['hadir'] }}</td>
                                    <td class="px-4 py-3 text-center">
                                        <a href="{{ route('laporan.karyawan.detail', $row['user']->id) }}"
                                            class="inline-flex items-center gap-1 px-3 py-1 bg-[#8E412E] text-white rounded-md text-xs hover:bg-[#BA6F4D] shadow">
                                            <i class="bi bi-eye-fill"></i> Lihat
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // Auto submit saat dropdown divisi diubah
            document.getElementById('divisi').addEventListener('change', function () {
                document.getElementById('filterForm').submit();
            });

            // ✅ Auto submit saat dropdown jenis guru diubah
            document.getElementById('jenis_guru').addEventListener('change', function () {
                document.getElementById('filterForm').submit();
            });

            // Hapus baris kategori guru sebelum datatable inisialisasi
            $('#laporanTable tbody tr[data-ignore="true"]').remove();

            $('#laporanTable').DataTable({
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
                order: [[0, 'asc']]
            });
        });
    </script>
@endpush

</x-app-layout>
