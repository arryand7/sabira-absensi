<x-app-shell>
<div class="mt-6 w-full sm:px-6 lg:px-8 space-y-6">
        <div class="bg-[var(--sabira-surface)] shadow-md rounded-2xl p-6">

            <h2 class="text-xl font-semibold text-[var(--sabira-ink)] mb-4">Daftar Divisi</h2>

            {{-- Tombol Tambah --}}
            <div class="mb-4">
                <a href="{{ route('divisis.create') }}"
                   class="inline-flex items-center gap-2 bg-[var(--sabira-primary)] text-white px-4 py-2 rounded-md hover:bg-[var(--sabira-primary-active)] shadow">
                    <i class="bi bi-plus-circle-fill"></i> Tambah Divisi
                </a>
            </div>

            {{-- Flash message --}}
            @if (session('success'))
                <div class="mb-4 text-green-600 font-medium">
                    {{ session('success') }}
                </div>
            @endif

            {{-- Tabel --}}
            <div class="overflow-x-auto">
                <table class="w-full table-auto text-left text-sm text-[var(--sabira-body)]">
                    <thead class="bg-[var(--sabira-neutral-strong)] text-white uppercase text-xs font-semibold">
                        <tr>
                            <th class="px-4 py-3">No</th>
                            <th class="px-4 py-3">Nama</th>
                            <th class="px-4 py-3">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#D6D8D2]">
                        @forelse($divisis as $divisi)
                            <tr class="hover:bg-[var(--sabira-surface-strong)] transition">
                                <td class="px-4 py-2">{{ $loop->iteration }}</td>
                                <td class="px-4 py-2">{{ $divisi->nama }}</td>
                                <td class="px-4 py-2 space-x-2">
                                    <a href="{{ route('divisis.edit', $divisi) }}"
                                       class="inline-flex items-center gap-1 px-3 py-1 bg-yellow-500 text-white text-xs rounded hover:bg-yellow-600 shadow">
                                        <i class="bi bi-pencil-fill"></i> Edit
                                    </a>
                                    <form action="{{ route('divisis.destroy', $divisi) }}" method="POST" class="delete-form inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit"
                                                class="inline-flex items-center gap-1 px-3 py-1 bg-red-600 text-white text-xs rounded hover:bg-red-700 shadow">
                                            <i class="bi bi-trash-fill"></i> Hapus
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="px-4 py-4 text-center text-gray-500">Belum ada data divisi.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</x-app-shell>
