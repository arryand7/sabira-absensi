<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200">Manajemen Kelas</h2>
    </x-slot>

    <div class="flex">
        <x-admin-sidenav />

        <div class="mt-6 w-full sm:px-6 lg:px-8 space-y-6">
            <div class="bg-white dark:bg-gray-800 shadow rounded-xl p-6">
                <a href="{{ route('admin.class-groups.create') }}"
                   class="mb-4 inline-block bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">
                    + Tambah Kelas
                </a>

                <table class="w-full table-auto text-left text-sm text-gray-500 dark:text-gray-400">
                    <thead class="bg-gray-100 dark:bg-gray-700">
                        <tr>
                            <th class="px-4 py-2">Nama Kelas</th>
                            <th class="px-4 py-2">Jenis Kelas</th>
                            <th class="px-4 py-2">Tahun Ajaran</th>
                            <th class="px-4 py-2">Wali Kelas</th>
                            <th class="px-4 py-2">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($classGroups as $group)
                            <tr class="border-b">
                                <td class="px-4 py-2">{{ $group->nama_kelas }}</td>
                                <td class="px-4 py-2 capitalize">{{ $group->jenis_kelas }}</td>
                                <td class="px-4 py-2">{{ $group->tahun_ajaran }}</td>
                                <td class="px-4 py-2">
                                    {{ $group->waliKelas?->user?->name ?? '-' }}
                                </td>
                                <td class="px-4 py-2 flex items-center gap-2">
                                    <a href="{{ route('admin.class-groups.edit', $group->id) }}"
                                    class="px-3 py-1 bg-yellow-500 text-white text-xs rounded hover:bg-yellow-600">
                                        Edit
                                    </a>
                                    <form action="{{ route('admin.class-groups.destroy', $group->id) }}" method="POST" class="delete-form inline">
                                        @csrf
                                        @method('DELETE')
                                        <button class="bg-red-600 text-white px-3 py-1 rounded hover:bg-red-700">
                                            Hapus
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-4 py-4 text-center text-gray-500">Belum ada kelas</td>
                            </tr>
                        @endforelse
                    </tbody>

                </table>
            </div>

            <script>
            document.querySelectorAll('.delete-form').forEach(form => {
                form.addEventListener('submit', function (e) {
                    e.preventDefault(); // cegah submit langsung

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
        </div>
    </div>
</x-app-layout>
