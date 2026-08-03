<x-app-shell>
    <h2 class="font-semibold text-xl text-[var(--sabira-ink)]">
        Jadwal untuk: {{ $teacher->name }}
    </h2>
<div class="mt-6 w-full sm:px-6 lg:px-8 space-y-6">
        <div class="bg-[var(--sabira-surface)] shadow-md rounded-2xl p-6">
            {{-- Tombol Tambah --}}
            <div class="mb-4">
                <a href="{{ route('admin.schedules.create', ['guru_id' => $teacher->id]) }}"
                   class="inline-flex items-center gap-1 bg-[var(--sabira-primary)] hover:bg-[var(--sabira-primary-active)] text-white font-medium px-4 py-2 rounded shadow">
                    <i class="bi bi-plus-circle-fill"></i> Tambah Jadwal
                </a>
            </div>

            @if($schedules->isEmpty())
                <p class="text-gray-500">Belum ada jadwal.</p>
            @else
                {{-- Table --}}
                <div class="overflow-x-auto">
                    <table id="jadwalTable" class="w-full text-sm text-left text-[var(--sabira-body)]">
                        <thead class="bg-[var(--sabira-neutral-strong)] text-white uppercase text-xs font-semibold">
                            <tr>
                                <th class="px-4 py-3">No</th>
                                <th class="px-4 py-3">Mata Pelajaran</th>
                                <th class="px-4 py-3">Tipe Kelas</th>
                                <th class="px-4 py-3">Kelas</th>
                                <th class="px-4 py-3">Hari</th>
                                <th class="px-4 py-3">Jam</th>
                                <th class="px-4 py-3 text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-[#D6D8D2]">
                            @foreach($schedules as $schedule)
                                <tr class="hover:bg-[var(--sabira-surface-strong)] transition">
                                    <td class="px-4 py-3 text-center">{{ $loop->iteration }}</td>
                                    <td class="px-4 py-3">{{ $schedule->subject->nama_mapel }}</td>
                                    <td class="px-4 py-3 capitalize">{{ $schedule->classGroup->jenis_kelas }}</td>
                                    <td class="px-4 py-3">{{ $schedule->classGroup->nama_kelas }}</td>
                                    <td class="px-4 py-3">{{ $schedule->hari }}</td>
                                    <td class="px-4 py-3">{{ $schedule->jam_mulai }} - {{ $schedule->jam_selesai }}</td>
                                    <td class="px-4 py-3 text-center space-x-1">
                                        <a href="{{ route('admin.schedules.edit', $schedule->id) }}"
                                           class="inline-flex items-center gap-1 px-3 py-1 bg-yellow-600 text-white text-xs rounded hover:bg-yellow-700 shadow">
                                            <i class="bi bi-pencil-fill"></i> Edit
                                        </a>
                                        <form action="{{ route('admin.schedules.destroy', $schedule->id) }}"
                                              method="POST" class="inline delete-form"
                                              onsubmit="return confirm('Hapus jadwal ini? Data yang dihapus tidak dapat dikembalikan.')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit"
                                                    class="inline-flex items-center gap-1 px-3 py-1 bg-red-600 text-white text-xs rounded hover:bg-red-700 shadow">
                                                <i class="bi bi-trash-fill"></i> Hapus
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>

</x-app-shell>
