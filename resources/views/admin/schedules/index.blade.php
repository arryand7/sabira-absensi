<x-app-layout>
    <div class="sm:px-6 lg:px-8">
        <x-page-title title="DAFTAR JADWAL" />
    </div>

    <x-slot name="sidebar">
        <x-admin-sidenav />
    </x-slot>

    <div class="mt-6 w-full sm:px-6 lg:px-8 space-y-6">
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <div class="bg-[#EFF0ED] border border-[#D6D8D2] rounded-xl p-4">
                <p class="text-xs uppercase tracking-wide text-[#8D9382]">Total Jadwal</p>
                <p class="mt-2 text-2xl font-semibold text-[#1C1E17]">{{ $summary['total'] }}</p>
            </div>
            <div class="bg-[#ECFDF5] border border-[#A7F3D0] rounded-xl p-4">
                <p class="text-xs uppercase tracking-wide text-[#065F46]">Guru Terjadwal</p>
                <p class="mt-2 text-2xl font-semibold text-[#065F46]">{{ $summary['teachers'] }}</p>
            </div>
            <div class="bg-[#E0E7FF] border border-[#A5B4FC] rounded-xl p-4">
                <p class="text-xs uppercase tracking-wide text-[#3730A3]">Kelas Terjadwal</p>
                <p class="mt-2 text-2xl font-semibold text-[#3730A3]">{{ $summary['classes'] }}</p>
            </div>
        </div>

        <div class="bg-[#EEF3E9] shadow-md rounded-2xl p-6 space-y-4">
            <form action="{{ route('admin.schedules.index') }}" method="GET" class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
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
                    <select name="class_group_id" class="mt-1 w-full rounded-md border-gray-300 shadow-sm focus:ring focus:ring-orange-200">
                        <option value="">Semua</option>
                        @foreach($classGroups as $group)
                            <option value="{{ $group->id }}" {{ request('class_group_id') == $group->id ? 'selected' : '' }}>
                                {{ $group->nama_kelas }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Mata Pelajaran</label>
                    <select name="subject_id" class="mt-1 w-full rounded-md border-gray-300 shadow-sm focus:ring focus:ring-orange-200">
                        <option value="">Semua</option>
                        @foreach($subjects as $subject)
                            <option value="{{ $subject->id }}" {{ request('subject_id') == $subject->id ? 'selected' : '' }}>
                                {{ $subject->nama_mapel }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="sm:col-span-2 lg:col-span-4 flex flex-wrap items-center gap-2">
                    <button type="submit"
                        class="bg-[#8E412E] text-white px-4 py-2 rounded-md hover:bg-[#BA6F4D] flex items-center gap-2 shadow">
                        <i class="bi bi-funnel-fill"></i> Tampilkan
                    </button>
                    <a href="{{ route('admin.schedules.index') }}"
                        class="bg-gray-500 text-white px-4 py-2 rounded-md hover:bg-gray-600 flex items-center gap-2 shadow">
                        <i class="bi bi-x-circle-fill"></i> Reset
                    </a>
                    <a href="{{ route('admin.schedules.create', ['guru_id' => request('guru_id'), 'tahun_ajaran' => request('tahun_ajaran')]) }}"
                        class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700 flex items-center gap-2 shadow">
                        <i class="bi bi-plus-circle-fill"></i> Tambah Jadwal
                    </a>
                    @if(request('guru_id'))
                        <a href="{{ route('admin.schedules.show-by-teacher', request('guru_id')) }}"
                            class="bg-[#8D9382] text-white px-4 py-2 rounded-md hover:bg-[#7A816F] flex items-center gap-2 shadow">
                            <i class="bi bi-person-badge-fill"></i> Kelola Jadwal Guru
                        </a>
                    @endif
                </div>
            </form>
        </div>

        <div class="bg-[#EEF3E9] shadow-md rounded-2xl p-6 schedule-table">
            <div class="overflow-x-auto">
                <table id="jadwalTable" class="w-full text-[11px] text-left text-[#373C2E]">
                    <thead class="bg-[#8D9382] text-white uppercase text-[11px] font-semibold">
                        <tr>
                            <th class="px-3 py-2">Hari</th>
                            <th class="px-3 py-2">Mulai</th>
                            <th class="px-3 py-2">Selesai</th>
                            <th class="px-3 py-2">Kelas</th>
                            <th class="px-3 py-2">Kode</th>
                            <th class="px-3 py-2">Mata Pelajaran</th>
                            <th class="px-3 py-2">Guru</th>
                            <th class="px-3 py-2 text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#D6D8D2] bg-white">
                        @forelse($schedules as $schedule)
                            <tr class="hover:bg-[#EFF0ED] transition">
                                <td class="px-3 py-2">{{ $schedule->hari }}</td>
                                <td class="px-3 py-2">{{ $schedule->jam_mulai }}</td>
                                <td class="px-3 py-2">{{ $schedule->jam_selesai }}</td>
                                <td class="px-3 py-2">{{ $schedule->classGroup->nama_kelas }}</td>
                                <td class="px-3 py-2">{{ $schedule->subject->kode_mapel ?? '-' }}</td>
                                <td class="px-3 py-2">{{ $schedule->subject->nama_mapel }}</td>
                                <td class="px-3 py-2">{{ $schedule->teacher->name }}</td>
                                <td class="px-3 py-2 text-center space-x-1">
                                    <a href="{{ route('admin.schedules.edit', $schedule->id) }}"
                                        class="inline-flex items-center gap-1 px-2.5 py-1 bg-yellow-600 text-white text-[10px] rounded hover:bg-yellow-700 shadow">
                                        <i class="bi bi-pencil-fill"></i> Edit
                                    </a>
                                    <form action="{{ route('admin.schedules.destroy', $schedule->id) }}"
                                        method="POST" class="inline delete-form">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit"
                                            class="inline-flex items-center gap-1 px-2.5 py-1 bg-red-600 text-white text-[10px] rounded hover:bg-red-700 shadow">
                                            <i class="bi bi-trash-fill"></i> Hapus
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="px-4 py-6 text-center text-[#8D9382]">
                                    Belum ada jadwal untuk filter yang dipilih.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="bg-[#EEF3E9] shadow-md rounded-2xl p-6">
            <h3 class="text-sm font-semibold text-[#1C1E17] mb-3">Import Jadwal</h3>

            @if (session('success') || session('errors_import'))
                <div class="mb-4 space-y-2">
                    @if (session('success') && is_array(session('success')))
                        <div class="mb-4 px-4 py-2 bg-green-100 text-green-700 rounded shadow space-y-1">
                            @foreach (session('success') as $msg)
                                <div>{{ $msg }}</div>
                            @endforeach
                        </div>
                    @endif

                    @if (session('errors_import'))
                        <div class="bg-red-100 text-red-800 px-4 py-3 rounded shadow">
                            <strong>Gagal:</strong>
                            <ul class="list-disc ml-5 text-sm">
                                @foreach (session('errors_import') as $msg)
                                    <li>{{ $msg }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                </div>
            @endif

            @error('file')
                <div class="bg-red-100 text-red-800 px-4 py-2 rounded shadow mt-2 text-sm">
                    {{ $message }}
                </div>
            @enderror

            <form action="{{ route('admin.schedules.import') }}" method="POST" enctype="multipart/form-data" class="flex flex-wrap items-center gap-3">
                @csrf
                <input type="file" name="file" required class="form-input" />
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded">
                    <i class="bi bi-upload"></i> Import
                </button>
            </form>
        </div>
    </div>

    @push('styles')
        <style>
            .schedule-table .dataTables_wrapper {
                font-size: 12px;
            }
            .schedule-table .dataTables_length label,
            .schedule-table .dataTables_filter label {
                margin: 0;
                font-weight: 600;
                color: #1C1E17;
            }
            .schedule-table .dataTables_filter input,
            .schedule-table .dataTables_length select {
                height: 28px;
                padding: 0 8px;
                font-size: 12px;
            }
            .schedule-table .dataTables_paginate .pagination {
                margin: 0;
            }
            .schedule-table table.dataTable thead th,
            .schedule-table table.dataTable tbody td {
                padding: 8px 10px;
            }
            .schedule-table table.dataTable {
                border-collapse: collapse !important;
            }
        </style>
    @endpush

    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                $('#jadwalTable').DataTable({
                    dom: "<'flex flex-wrap items-center gap-3 mb-3'<'dataTables_length' l><'dataTables_filter' f>>t<'flex flex-wrap items-center justify-between gap-3 mt-3' i p>",
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
                    order: [],
                    columnDefs: [
                        { orderable: false, targets: 7 }
                    ],
                });
            });
        </script>
    @endpush
</x-app-layout>
