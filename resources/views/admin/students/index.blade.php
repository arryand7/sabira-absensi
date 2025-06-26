<x-app-layout>
    <x-slot name="sidebar">
            <x-admin-sidenav />
        </x-slot>
    <div class="mt-6 w-full sm:px-6 lg:px-8 space-y-6">
        {{-- <x-admin-sidenav /> --}}

        <main class="flex-1 max-w-full overflow-x-auto">
            <div class="flex flex-wrap items-center justify-between mb-6 gap-4">
                <a href="{{ route('admin.students.create') }}"
                    class="inline-block bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded shadow transition">
                    + Tambah Murid
                </a>

                <form action="{{ route('admin.students.import') }}" method="POST" enctype="multipart/form-data" class="flex items-center gap-3">
                    @csrf
                    <input type="file" name="file" required
                        class="border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 text-gray-900" />
                    <button type="submit"
                        class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded shadow transition">
                        Import Excel
                    </button>
                </form>

                <form id="bulk-delete-form" action="{{ route('admin.students.bulk-delete') }}" method="POST">
                    @csrf
                    <input type="hidden" name="student_ids_json" id="student_ids_json" />

                    <button type="submit"
                        class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded shadow transition mb-4"
                        onclick="return confirm('Yakin ingin menghapus murid terpilih?')">
                        Hapus Murid Terpilih
                    </button>
                </form>
            </div>

            <form method="GET" action="{{ route('admin.students.index') }}"
                class="flex flex-wrap gap-6 mb-8 items-end max-w-full">
                <div class="flex flex-col">
                    <label for="kelas_formal" class="text-sm font-medium mb-1 text-gray-900">Kelas Formal</label>
                    <select id="kelas_formal" name="kelas_formal"
                        class="border border-gray-300 rounded px-3 py-2 w-48 focus:outline-none focus:ring-2 focus:ring-blue-500 text-gray-900">
                        <option value="">Semua</option>
                        @foreach ($academicClasses as $class)
                            <option value="{{ $class->id }}" {{ request('kelas_formal') == $class->id ? 'selected' : '' }}>
                                {{ $class->nama_kelas }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="flex flex-col">
                    <label for="kelas_muadalah" class="text-sm font-medium mb-1 text-gray-900">Kelas Muadalah</label>
                    <select id="kelas_muadalah" name="kelas_muadalah"
                        class="border border-gray-300 rounded px-3 py-2 w-48 focus:outline-none focus:ring-2 focus:ring-blue-500 text-gray-900">
                        <option value="">Semua</option>
                        @foreach ($muadalahClasses as $class)
                            <option value="{{ $class->id }}" {{ request('kelas_muadalah') == $class->id ? 'selected' : '' }}>
                                {{ $class->nama_kelas }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="flex flex-col">
                    <label for="jenis_kelamin" class="text-sm font-medium mb-1 text-gray-900">Jenis Kelamin</label>
                    <select id="jenis_kelamin" name="jenis_kelamin"
                        class="border border-gray-300 rounded px-3 py-2 w-40 focus:outline-none focus:ring-2 focus:ring-blue-500 text-gray-900">
                        <option value="">Semua</option>
                        <option value="L" {{ request('jenis_kelamin') == 'L' ? 'selected' : '' }}>Laki-laki</option>
                        <option value="P" {{ request('jenis_kelamin') == 'P' ? 'selected' : '' }}>Perempuan</option>
                    </select>
                </div>

                <div class="flex items-center gap-2">
                    <button type="submit"
                        class="bg-blue-600 hover:bg-blue-700 text-white px-5 py-2 rounded shadow transition">
                        Filter
                    </button>
                    <a href="{{ route('admin.students.index') }}" class="text-gray-600 hover:underline">Reset</a>
                </div>
            </form>

            <!-- Table -->
            <div class="bg-white shadow rounded-xl p-6 overflow-x-auto">
                <table id="studentTable" class="stripe hover w-full text-sm text-left text-gray-800">
                    <thead class="bg-blue-100 text-blue-800 uppercase text-xs font-semibold">
                        <tr>
                            <th class="px-4 py-2">
                                <input type="checkbox" id="select-all" />
                            </th>
                            <th class="px-4 py-2">Nama</th>
                            <th class="px-4 py-2">NIS</th>
                            <th class="px-4 py-2">Kelas Formal</th>
                            <th class="px-4 py-2">Kelas Muadalah</th>
                            <th class="px-4 py-2">Jenis Kelamin</th>
                            <th class="px-4 py-2 text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-blue-50">
                        @foreach ($students as $student)
                            <tr class="border-b hover:bg-sky-50 dark:border-gray-700 dark:hover:bg-gray-700 transition">
                                <td class="px-4 py-2">
                                    <input type="checkbox" name="student_ids[]" value="{{ $student->id }}" class="student-checkbox" />
                                </td>
                                <td class="px-4 py-2">{{ $student->nama_lengkap }}</td>
                                <td class="px-4 py-2">{{ $student->nis }}</td>
                                <td class="px-4 py-2">{{ $student->classGroups->firstWhere('jenis_kelas', 'formal')?->nama_kelas ?? '-' }}</td>
                                <td class="px-4 py-2">{{ $student->classGroups->firstWhere('jenis_kelas', 'muadalah')?->nama_kelas ?? '-' }}</td>
                                <td class="px-4 py-2">{{ $student->jenis_kelamin }}</td>
                                <td class="px-4 py-2 space-x-2 text-center">
                                    <a href="{{ route('admin.students.edit', $student->id) }}"
                                        class="bg-yellow-500 text-white px-3 py-1 rounded hover:bg-yellow-600">
                                        Edit
                                    </a>
                                    <form action="{{ route('admin.students.destroy', $student->id) }}" method="POST" class="inline delete-form">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit"
                                            class="bg-red-600 text-white px-3 py-1 rounded hover:bg-red-700">
                                            Hapus
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach

                        @if($students->isEmpty())
                            <tr>
                                <td colspan="6" class="px-4 py-4 text-center text-gray-500 dark:text-gray-400">
                                    Belum ada data murid.
                                </td>
                            </tr>
                        @endif
                    </tbody>
                </table>
            </div>
        </main>
    </div>

    <!-- DataTables CDN -->

    <script>
        $(document).ready(function () {
            $('#studentTable').DataTable({
                responsive: true,
                language: {
                    search: "Cari:",
                    lengthMenu: "Tampilkan _MENU_ entri",
                    info: "Menampilkan _START_ sampai _END_ dari _TOTAL_ entri",
                    paginate: {
                        first: "Awal",
                        last: "Akhir",
                        next: "→",
                        previous: "←"
                    },
                    emptyTable: "Belum ada data murid."
                }
            });

            // Centang semua checkbox
            $('#select-all').on('click', function () {
                $('.student-checkbox').prop('checked', this.checked);
            });

            // Submit bulk delete
            $('#bulk-delete-form').on('submit', function (e) {
                const selectedIds = $('.student-checkbox:checked').map(function () {
                    return this.value;
                }).get();

                if (selectedIds.length === 0) {
                    e.preventDefault();
                    alert('Pilih minimal satu murid terlebih dahulu.');
                } else {
                    $('#student_ids_json').val(JSON.stringify(selectedIds));
                }
            });

            // Konfirmasi sebelum hapus individual
            $('.delete-form').on('submit', function (e) {
                e.preventDefault();
                const form = this;
                Swal.fire({
                    title: 'Yakin ingin menghapus?',
                    text: "Data yang dihapus tidak bisa dikembalikan!",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: 'Ya, hapus!',
                    cancelButtonText: 'Batal'
                }).then((result) => {
                    if (result.isConfirmed) {
                        form.submit();
                    }
                });
            });
        });

    </script>

    @if($errors->any())
        <script>
            Swal.fire({
                icon: 'error',
                title: 'Terjadi Kesalahan!',
                html: `{!! implode('<br>', $errors->all()) !!}`,
                confirmButtonText: 'OK'
            });
        </script>
    @endif
</x-app-layout>
