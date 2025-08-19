<x-app-layout>
    <x-slot name="sidebar">
        <x-admin-sidenav />
    </x-slot>

    <div class="w-full mt-6 sm:px-6 lg:px-8 space-y-6">
        {{-- Ringkasan Statistik --}}
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            @php
                $stats = [
                    ['title' => 'Total Karyawan', 'value' => $totalKaryawan],
                    ['title' => 'Absensi Hari Ini', 'value' => $totalSudahAbsen],
                    ['title' => 'Belum Absen', 'value' => $totalBelumHadir],
                ];
            @endphp

            @foreach ($stats as $item)
                <div class="bg-[#EFF0ED] p-5 rounded-2xl shadow-md border border-[#D6D8D2]">
                    <h3 class="text-sm font-medium text-[#5C644C]">{{ $item['title'] }}</h3>
                    <p class="mt-2 text-4xl font-bold text-[#1C1E17]">{{ $item['value'] }}</p>
                </div>
            @endforeach
        </div>

        {{-- Laporan Absensi Hari Ini --}}
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            {{-- Tabel: Absensi Hari Ini --}}
            <div class="lg:col-span-2 bg-[#EEF3E9] p-6 rounded-2xl shadow-md border border-[#D6D8D2]">
                <h3 class="text-lg font-semibold text-[#292D22] mb-4">Absensi Hari Ini</h3>

                <div class="overflow-x-auto rounded-xl">
                    <table id="tabel-absensi" class="min-w-full text-sm text-[#292D22]">
                        <thead class="bg-[#8D9382] text-white text-left text-xs font-semibold uppercase">
                            <tr>
                                <th class="px-4 py-3">Nama</th>
                                <th class="px-4 py-3">Tanggal</th>
                                <th class="px-4 py-3">Check In</th>
                                <th class="px-4 py-3">Check Out</th>
                                <th class="px-4 py-3">Status</th>
                                <th class="px-4 py-3">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-[#D6D8D2]">
                            @foreach ($absensis as $absen)
                                <tr class="hover:bg-[#BEC1B7] transition">
                                    <td class="px-4 py-3">{{ $absen->user->name }}</td>
                                    <td class="px-4 py-3">{{ \Carbon\Carbon::parse($absen->waktu_absen)->format('d M Y') }}</td>
                                    <td class="px-4 py-3">{{ $absen->check_in ?? '-' }}</td>
                                    <td class="px-4 py-3">{{ $absen->check_out ?? '-' }}</td>
                                    <td class="px-4 py-3">
                                        @php
                                            $statusColor = match($absen->status) {
                                                'Hadir' => 'bg-green-100 text-green-700',
                                                'Terlambat' => 'bg-yellow-100 text-yellow-700',
                                                default => 'bg-red-100 text-red-700',
                                            };
                                        @endphp
                                        <span class="inline-block px-3 py-1 rounded-full text-xs font-semibold {{ $statusColor }}">
                                            {{ $absen->status ?? '-' }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3">
                                        <button data-modal-target="modal-edit-{{ $absen->id }}" data-modal-toggle="modal-edit-{{ $absen->id }}"
                                            class="text-sm text-blue-600 hover:underline flex items-center gap-1">
                                            <i class="bi bi-pencil-square"></i>Edit
                                        </button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- Tabel: Karyawan Belum Absen --}}
            <div class="bg-[#EEF3E9] p-6 rounded-2xl shadow-md border border-[#D6D8D2] h-fit">
                <h3 class="text-lg font-semibold text-[#292D22] mb-4">Belum Absen</h3>

                <div class="overflow-x-auto rounded-xl">
                    <table id="tabel-belum-absen" class="min-w-full text-sm text-[#292D22]">
                        <thead class="bg-[#8D9382] text-white text-left text-xs font-semibold uppercase">
                            <tr>
                                <th class="px-4 py-3">Nama</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-[#D6D8D2]">
                            @foreach ($karyawanBelumAbsen as $karyawan)
                                <tr class="hover:bg-[#BEC1B7] transition">
                                    <td class="px-4 py-3 flex justify-between items-center">
                                        {{ $karyawan->user->name }}
                                        <form action="{{ route('admin.absensi.manual.store') }}" method="POST" class="flex gap-2 items-center">
                                            @csrf
                                            <input type="hidden" name="user_id" value="{{ $karyawan->user_id }}">
                                            <button type="submit" class="px-3 py-1 bg-green-600 text-white text-xs rounded hover:bg-green-700">
                                                Absenkan
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- Modals for Edit --}}
        @foreach ($absensis as $absen)
            <div id="modal-edit-{{ $absen->id }}" tabindex="-1" aria-hidden="true"
                class="hidden fixed top-0 left-0 right-0 z-50 flex items-center justify-center w-full h-full bg-black/50">
                <div class="relative p-6 w-full max-w-md bg-white rounded-lg shadow">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-medium text-gray-900">Edit Absensi</h3>
                        <button type="button" class="text-gray-400 hover:text-gray-900" data-modal-hide="modal-edit-{{ $absen->id }}">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd"
                                    d="M10 8.586l4.95-4.95 1.414 1.414L11.414 10l4.95 4.95-1.414 1.414L10 11.414l-4.95 4.95-1.414-1.414L8.586 10 3.636 5.05l1.414-1.414L10 8.586z"
                                    clip-rule="evenodd" />
                            </svg>
                        </button>
                    </div>
                    <form action="{{ route('admin.absensi.update', $absen->id) }}" method="POST">
                        @csrf
                        @method('PUT')
                        <div class="mb-3">
                            <label class="block text-sm font-medium">Nama</label>
                            <input type="text" value="{{ $absen->user->name }}" class="w-full bg-gray-100 rounded px-3 py-2 text-sm" readonly>
                        </div>
                        {{-- Check In --}}
                        <div class="mb-3">
                            <label class="block text-sm font-medium">Check In</label>
                            <input type="time" name="check_in" value="{{ old('check_in', $absen->check_in) }}"
                                class="w-full rounded px-3 py-2 border text-sm">
                            @error('check_in')
                                <div class="text-red-500 text-sm mt-1">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Check Out --}}
                        <div class="mb-3">
                            <label class="block text-sm font-medium">Check Out</label>
                            <input type="time" name="check_out" value="{{ old('check_out', $absen->check_out) }}"
                                class="w-full rounded px-3 py-2 border text-sm">
                            @error('check_out')
                                <div class="text-red-500 text-sm mt-1">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="flex justify-end gap-2">
                            <button type="button" data-modal-hide="modal-edit-{{ $absen->id }}"
                                class="px-4 py-2 bg-gray-300 rounded text-sm">Batal</button>
                            <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded text-sm">Simpan</button>
                        </div>
                    </form>
                </div>
            </div>
        @endforeach
    </div>

    <script src="https://unpkg.com/flowbite@1.6.5/dist/flowbite.min.js"></script>
    <script>
        $(document).ready(function () {
            $('#tabel-absensi').DataTable({
                responsive: true,
                pageLength: 10,
                language: {
                    search: "Cari:",
                    lengthMenu: "Tampilkan _MENU_ entri",
                    info: "Menampilkan _START_ sampai _END_ dari _TOTAL_ entri",
                    paginate: {
                        first: "Pertama",
                        last: "Terakhir",
                        next: ">>",
                        previous: "<<"
                    },
                    zeroRecords: "Belum Ada Yang Absen",
                }
            });

            $('#tabel-belum-absen').DataTable({
                pageLength: 10,
                paging: true,
                searching: true,
                info: true,
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
                    zeroRecords: "Sudah Absen Semua",
                },
                dom: 'ftip'
            });
        });
    </script>

    @if (session('openModal'))
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                const modalId = '{{ session('openModal') }}';
                const modal = document.getElementById(modalId);
                if (modal) {
                    modal.classList.remove('hidden');
                }
            });
        </script>
    @endif
</x-app-layout>
