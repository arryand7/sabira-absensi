<x-app-layout>
    <div class="py-4 mt-4 max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
        {{-- Judul dan Tombol Back --}}
        <div class="flex items-center justify-between mb-6">
            <h2 class="text-2xl font-bold text-gray-800">Jadwal Mengajar</h2>
            <a href="{{ route('dashboard') }}"
               class="inline-flex items-center px-3 py-1 text-gray-800 rounded hover:bg-gray-300 text-sm">
                 <i class="bi bi-arrow-left-circle-fill text-lg mr-1"></i>
            </a>
        </div>

        {{-- Tabel Jadwal --}}
        <div class="bg-white dark:bg-gray-800 shadow rounded-xl overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                <thead>
                    <tr class="bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 uppercase text-sm">
                        <th class="px-4 py-3 text-left">Mata Pelajaran</th>
                        <th class="px-4 py-3 text-left">Kode</th>
                        <th class="px-4 py-3 text-left">Hari</th>
                        <th class="px-4 py-3 text-left">Jam Mulai</th>
                        <th class="px-4 py-3 text-left">Jam Selesai</th>
                        <th class="px-4 py-3 text-left">Kelas</th>
                        <th class="px-4 py-3 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-700 text-gray-800 dark:text-gray-200">
                    @forelse ($schedules as $schedule)
                        <tr class="hover:bg-blue-50 dark:hover:bg-gray-700 transition duration-150">
                            <td class="px-4 py-2">{{ $schedule->subject->nama_mapel }}</td>
                            <td class="px-4 py-2">{{ $schedule->subject->kode_mapel }}</td>
                            <td class="px-4 py-2">{{ $schedule->hari }}</td>
                            <td class="px-4 py-2">{{ $schedule->jam_mulai }}</td>
                            <td class="px-4 py-2">{{ $schedule->jam_selesai }}</td>
                            <td class="px-4 py-2">{{ $schedule->classGroup->nama_kelas }}</td>
                            <td class="px-4 py-2 text-center">
                                <a href="{{ route('guru.schedule.absen', $schedule->class_group_id) }}"
                                   class="inline-block bg-blue-600 hover:bg-blue-700 text-white text-xs font-medium px-4 py-1 rounded">
                                    Absen
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center px-4 py-4 text-gray-500 dark:text-gray-400">
                                Belum ada jadwal mengajar.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</x-app-layout>
