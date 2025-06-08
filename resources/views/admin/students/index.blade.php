<x-app-layout>
    <div class="flex">
        <x-admin-sidenav />

        <main class="flex-1 p-8 max-w-full overflow-x-auto">
            {{-- <h1 class="text-3xl font-semibold mb-6 text-gray-800 dark:text-gray-200">Manajemen Murid</h1> --}}

            <div class="flex flex-wrap items-center justify-between mb-6 gap-4">
                <a href="{{ route('admin.students.create') }}"
                    class="inline-block bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded shadow transition">
                    + Tambah Murid
                </a>

                <!-- Import Excel -->
                <form action="{{ route('admin.students.import') }}" method="POST" enctype="multipart/form-data" class="flex items-center gap-3">
                    @csrf
                    <input type="file" name="file" required
                        class="border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 text-gray-900" />
                    <button type="submit"
                        class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded shadow transition">
                        Import Excel
                    </button>
                </form>
            </div>

            <!-- Filter -->
            <form method="GET" action="{{ route('admin.students.index') }}"
                class="flex flex-wrap gap-6 mb-8 items-end max-w-full">

                <div class="flex flex-col">
                    <label for="kelas_akademik" class="text-sm font-medium mb-1 text-gray-900">Kelas Akademik</label>
                    <select id="kelas_akademik" name="kelas_akademik"
                        class="border border-gray-300 rounded px-3 py-2 w-48 focus:outline-none focus:ring-2 focus:ring-blue-500 text-gray-900">
                        <option value="">Semua</option>
                        @foreach ($academicClasses as $class)
                            <option value="{{ $class->id }}" {{ request('kelas_akademik') == $class->id ? 'selected' : '' }}>
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


            <div class="bg-white dark:bg-gray-800 shadow rounded-xl p-6 overflow-x-auto max-h-[600px]">
                <table class="w-full table-auto text-left text-sm text-gray-500 dark:text-gray-400">
                    <thead class="bg-gray-100 dark:bg-gray-700">
                        <tr>
                            <th class="px-4 py-2">Nama</th>
                            <th class="px-4 py-2">NIS</th>
                            <th class="px-4 py-2">Kelas Akademik</th>
                            <th class="px-4 py-2">Kelas Muadalah</th>
                            <th class="px-4 py-2">Jenis Kelamin</th>
                            <th class="px-4 py-2 text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($students as $student)
                            <tr class="border-b dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700 transition">
                                <td class="px-4 py-2">{{ $student->nama_lengkap }}</td>
                                <td class="px-4 py-2">{{ $student->nis }}</td>
                                <td class="px-4 py-2">
                                    {{ $student->classGroups->firstWhere('jenis_kelas', 'akademik')?->nama_kelas ?? '-' }}
                                </td>
                                <td class="px-4 py-2">
                                    {{ $student->classGroups->firstWhere('jenis_kelas', 'muadalah')?->nama_kelas ?? '-' }}
                                </td>
                                <td class="px-4 py-2">{{ $student->jenis_kelamin }}</td>
                                <td class="px-4 py-2 space-x-2 text-center">
                                    <a href="{{ route('admin.students.edit', $student->id) }}"
                                        class="bg-yellow-500 text-white px-3 py-1 rounded hover:bg-yellow-600">
                                        Edit
                                    </a>
                                    <form action="{{ route('admin.students.destroy', $student->id) }}" method="POST" class="inline delete-form">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="bg-red-600 text-white px-3 py-1 rounded hover:bg-red-700">
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

    <script>
        document.querySelectorAll('.delete-form').forEach(form => {
            form.addEventListener('submit', function (e) {
                e.preventDefault();

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
