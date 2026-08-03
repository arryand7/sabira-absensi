<x-app-shell>
    <h2 class="font-semibold text-xl text-[var(--sabira-ink)] leading-tight">
        {{ __('Daftar Mata Pelajaran') }}
    </h2>
<div class="mt-6 w-full sm:px-6 lg:px-8 space-y-6">
        <div class="bg-[var(--sabira-surface)] shadow-md rounded-2xl p-6">

            {{-- Tombol Tambah --}}
            <div class="mb-4">
                <a href="{{ route('subjects.create') }}"
                   class="inline-flex items-center gap-2 bg-[var(--sabira-primary)] text-white px-4 py-2 rounded-md hover:bg-[var(--sabira-primary-active)] shadow">
                    <i class="bi bi-plus-circle-fill"></i> Tambah Mapel
                </a>
            </div>

            {{-- Tabel Mapel --}}
            <div class="overflow-x-auto">
                <table id="subjectTable" class="w-full table-auto text-left text-sm text-[var(--sabira-body)]">
                    <thead class="bg-[var(--sabira-neutral-strong)] text-white uppercase text-xs font-semibold">
                        <tr>
                            <th class="px-4 py-3">Nama</th>
                            <th class="px-4 py-3">Kode</th>
                            <th class="px-4 py-3">Jenis</th>
                            <th class="px-4 py-3">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#D6D8D2]">
                        @if ($subjects->count() > 0)
                            @foreach($subjects as $subject)
                                <tr class="hover:bg-[var(--sabira-surface-strong)] transition">
                                    <td class="px-4 py-2">{{ $subject->nama_mapel }}</td>
                                    <td class="px-4 py-2">{{ $subject->kode_mapel }}</td>
                                    <td class="px-4 py-2">
                                        @if ($subject->jenis_mapel == 'formal')
                                            Reguler
                                        @elseif($subject->jenis_mapel == 'muadalah')
                                            Non Reguler
                                        @endif
                                    </td>
                                    <td class="px-4 py-2 space-x-2">
                                        <a href="{{ route('subjects.edit', $subject) }}"
                                        class="inline-flex items-center gap-1 px-3 py-1 bg-yellow-500 text-white text-xs rounded hover:bg-yellow-600 shadow">
                                            <i class="bi bi-pencil-fill"></i> Edit
                                        </a>
                                        <form action="{{ route('subjects.destroy', $subject->id) }}" method="POST" class="delete-form inline">
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
                        @else
                            <tr>
                                <td colspan="4" class="px-4 py-4 text-center text-gray-500">Belum ada data mapel.</td>
                            </tr>
                        @endif
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            document.querySelectorAll('.delete-form').forEach((form) => {
                form.addEventListener('submit', (event) => {
                    if (!confirm('Hapus mata pelajaran ini? Data yang dihapus tidak dapat dikembalikan.')) {
                        event.preventDefault();
                    }
                });
            });
        });
    </script>
</x-app-shell>
