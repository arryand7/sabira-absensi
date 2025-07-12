<x-app-layout>
    <h2 class="font-semibold text-xl text-[#292D22] leading-tight">
        {{ __('Daftar Mata Pelajaran') }}
    </h2>

    <x-slot name="sidebar">
        <x-admin-sidenav />
    </x-slot>

    <div class="mt-6 w-full sm:px-6 lg:px-8 space-y-6">
        <div class="bg-[#EEF3E9] shadow-md rounded-2xl p-6">

            {{-- Tombol Tambah --}}
            <div class="mb-4">
                <a href="{{ route('subjects.create') }}"
                   class="inline-flex items-center gap-2 bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700 shadow">
                    <i class="bi bi-plus-circle-fill"></i> Tambah Mapel
                </a>
            </div>

            {{-- Tabel Mapel --}}
            <div class="overflow-x-auto">
                <table id="subjectTable" class="w-full table-auto text-left text-sm text-[#373C2E]">
                    <thead class="bg-[#8D9382] text-white uppercase text-xs font-semibold">
                        <tr>
                            <th class="px-4 py-3">Nama</th>
                            <th class="px-4 py-3">Kode</th>
                            <th class="px-4 py-3">Jenis</th>
                            <th class="px-4 py-3">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#D6D8D2]">
                        @forelse($subjects as $subject)
                            <tr class="hover:bg-[#BEC1B7] transition">
                                <td class="px-4 py-2">{{ $subject->nama_mapel }}</td>
                                <td class="px-4 py-2">{{ $subject->kode_mapel }}</td>
                                <td class="px-4 py-2 capitalize">{{ $subject->jenis_mapel }}</td>
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
                        @empty
                            <tr>
                                <td colspan="4" class="px-4 py-4 text-center text-gray-500">Belum ada data mapel.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Scripts --}}
    <script>
        $(document).ready(function () {
            $('#subjectTable').DataTable({
                pageLength: 10,
                order: [[0, 'asc']],
                language: {
                    search: "Cari:",
                    lengthMenu: "Tampilkan _MENU_ data",
                    info: "Menampilkan _START_ sampai _END_ dari _TOTAL_ data",
                    paginate: {
                        first: "Awal",
                        last: "Akhir",
                        next: "Berikutnya",
                        previous: "Sebelumnya"
                    },
                    zeroRecords: "Data tidak ditemukan"
                }
            });

            // SweetAlert konfirmasi hapus
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
        });
    </script>
</x-app-layout>
