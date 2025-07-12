<x-app-layout>
    <x-slot name="sidebar">
        <x-admin-sidenav />
    </x-slot>

    <div class="mt-6 w-full sm:px-6 lg:px-8 space-y-6">
        <div class="bg-[#EEF3E9] shadow-md rounded-2xl p-6">

            <h2 class="text-xl font-semibold text-[#292D22] mb-4">Master Data Sholat</h2>

            {{-- Form Tambah Sholat --}}
            <form method="POST" action="{{ route('admin.sholat.store') }}" class="mb-4 flex gap-2">
                @csrf
                <input type="text" name="nama" class="w-full px-4 py-2 border border-gray-300 rounded-md focus:ring focus:ring-blue-200" placeholder="Nama sholat (contoh: Subuh)" required>
                <button type="submit" class="bg-[#8D9382] text-white px-4 py-2 rounded-md hover:bg-blue-700 shadow">
                    <i class="bi bi-plus-circle-fill"></i> Tambah
                </button>
            </form>

            {{-- Tabel Sholat --}}
            <div class="overflow-x-auto">
                <table class="w-full table-auto text-left text-sm text-[#373C2E]">
                    <thead class="bg-[#8D9382] text-white uppercase text-xs font-semibold">
                        <tr>
                            <th class="px-4 py-3">Nama Sholat</th>
                            <th class="px-4 py-3">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#D6D8D2]">
                        @forelse($kegiatanSholat as $sholat)
                            <tr class="hover:bg-[#BEC1B7] transition">
                                <td class="px-4 py-2">{{ $sholat->nama }}</td>
                                <td class="px-4 py-2">
                                    <form method="POST" action="{{ route('admin.sholat.delete', $sholat->id) }}" class="delete-form inline">
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
                                <td colspan="2" class="px-4 py-4 text-center text-gray-500">Belum ada data sholat.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

        </div>
    </div>

</x-app-layout>
