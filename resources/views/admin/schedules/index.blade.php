<x-app-layout>
    <h2 class="font-semibold text-xl text-[#292D22]">
        {{ __('Daftar Guru') }}
    </h2>

    <x-slot name="sidebar">
        <x-admin-sidenav />
    </x-slot>

    <div class="mt-6 w-full sm:px-6 lg:px-8 space-y-6">
        <div class="bg-[#EEF3E9] shadow-md rounded-2xl p-6">
            @if (session('success'))
                <div class="mb-4 px-4 py-2 bg-green-100 text-green-700 rounded shadow">
                    {{ session('success') }}
                </div>
            @endif

            <div class="overflow-x-auto">
                <table id="guruTable" class="w-full text-sm text-left text-#373C2E">
                    <thead class="bg-[#8D9382] text-white uppercase text-xs font-semibold">
                        <tr>
                            <th class="px-4 py-3">No</th>
                            <th class="px-4 py-3">Nama</th>
                            <th class="px-4 py-3">Jenis</th>
                            <th class="px-4 py-3 text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#D6D8D2]">
                        @forelse ($teachers as $teacher)
                            <tr class="hover:bg-[#BEC1B7] transition">
                                <td class="px-4 py-3 text-center">{{ $loop->iteration }}</td>
                                <td class="px-4 py-3">{{ $teacher->name }}</td>
                                <td class="px-4 py-3 capitalize">{{ $teacher->guru->jenis ?? '-' }}</td>
                                <td class="px-4 py-3 text-center">
                                    <a href="{{ route('admin.schedules.show-by-teacher', $teacher->id) }}"
                                       class="inline-flex items-center gap-1 px-3 py-1 bg-[#8E412E] text-white rounded-md text-xs hover:bg-[#BA6F4D] shadow">
                                        <i class="bi bi-calendar-week-fill"></i> Jadwal
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center text-gray-500 py-4">
                                    Belum ada guru yang terdaftar.
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
            $(document).ready(function () {
                $('#guruTable').DataTable({
                    responsive: true,
                    pageLength: 10,
                    order: [[1, 'asc']],
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
                    }
                });
            });
        </script>
    @endpush
</x-app-layout>
