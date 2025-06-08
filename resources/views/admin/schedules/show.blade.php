<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Jadwal untuk:') }} {{ $teacher->name }} ({{ $teacher->guru->jenis ?? '-' }})
        </h2>
    </x-slot>

    <div class="flex">
        <x-admin-sidenav />

        <div class="mt-6 w-full sm:px-6 lg:px-8 space-y-6">
            <div class="bg-white dark:bg-gray-800 shadow rounded-xl p-6">

                <div class="mb-4">
                    <a href="{{ route('admin.schedules.create', ['guru_id' => $teacher->id]) }}"
                       class="bg-blue-600 hover:bg-blue-700 text-white font-semibold px-4 py-2 rounded">
                        + Tambah Jadwal
                    </a>
                </div>

                @if($schedules->isEmpty())
                    <p class="text-gray-500 dark:text-gray-400">Belum ada jadwal.</p>
                @else
                    <table class="w-full table-auto text-left text-sm text-gray-700 dark:text-gray-300">
                        <thead class="bg-gray-100 dark:bg-gray-700">
                            <tr>
                                <th class="px-4 py-2">No</th>
                                <th class="px-4 py-2">Mata Pelajaran</th>
                                <th class="px-4 py-2">Tipe Kelas</th>
                                <th class="px-4 py-2">Kelas</th>
                                <th class="px-4 py-2">Hari</th>
                                <th class="px-4 py-2">Jam</th>
                                <th class="px-4 py-2 text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($schedules as $schedule)
                                <tr class="border-b dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700">
                                    <td class="px-4 py-2 text-center">{{ $loop->iteration }}</td>
                                    <td class="px-4 py-2">{{ $schedule->subject->nama_mapel }}</td>
                                    <td class="px-4 py-2 capitalize">{{ $schedule->classGroup->jenis_kelas }}</td>
                                    <td class="px-4 py-2">{{ $schedule->classGroup->nama_kelas }}</td>
                                    <td class="px-4 py-2">{{ $schedule->hari }}</td>
                                    <td class="px-4 py-2">{{ $schedule->jam_mulai }} - {{ $schedule->jam_selesai }}</td>
                                    <td class="px-4 py-2 text-center space-x-2">
                                        <a href="{{ route('admin.schedules.edit', $schedule->id) }}"
                                           class="bg-yellow-500 text-white px-3 py-1 rounded hover:bg-yellow-600">
                                            Edit
                                        </a>
                                        <form action="{{ route('admin.schedules.destroy', $schedule->id) }}"
                                              method="POST" class="inline delete-form">
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
                        </tbody>
                    </table>
                @endif

            </div>
        </div>
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
</x-app-layout>
