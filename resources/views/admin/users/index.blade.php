<x-app-layout>
    <h2 class="font-semibold text-xl text-[#292D22]">Manajemen User</h2>
    <x-slot name="sidebar">
        <x-admin-sidenav />
    </x-slot>

    <div class="flex">

        <div class="mt-6 w-full sm:px-6 lg:px-8 space-y-6">
            <div class="bg-[#EEF3E9] shadow-md rounded-2xl p-6">
                {{-- Tombol Tambah User --}}
                <div class="mb-4">
                    <a href="{{ route('users.create') }}"
                       class="inline-flex items-center gap-2 bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700 shadow">
                        <i class="bi bi-person-plus-fill"></i> Tambah User
                    </a>
                </div>

                {{-- Tabel User --}}
                <div class="overflow-x-auto">
                    <table class="w-full table-auto text-left text-sm text-[#373C2E]">
                        <thead class="bg-[#8D9382] text-white uppercase text-xs font-semibold">
                            <tr>
                                <th class="px-4 py-3">Nama</th>
                                <th class="px-4 py-3">Role</th>
                                <th class="px-4 py-3">Email</th>
                                <th class="px-4 py-3">Status</th>
                                <th class="px-4 py-3">Divisi/Jenis</th> {{-- kolom baru --}}
                                <th class="px-4 py-3">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-[#D6D8D2]">
                            @foreach($users as $user)
                                <tr class="hover:bg-[#BEC1B7] transition">
                                    <td class="px-4 py-2">{{ $user->name }}</td>
                                    <td class="px-4 py-2 capitalize">{{ $user->role }}</td>
                                    <td class="px-4 py-2">{{ $user->email }}</td>
                                    <td class="px-4 py-2 capitalize {{ $user->status == 'aktif' ? 'text-green-600' : 'text-red-600' }}">
                                        {{ $user->status }}
                                    </td>

                                    {{-- Kolom Divisi/Jenis --}}
                                    <td class="px-4 py-2">
                                        @if($user->role === 'karyawan')
                                            {{ $user->karyawan->divisi->nama ?? '-' }}
                                        @elseif($user->role === 'guru')
                                            {{ ucfirst($user->guru->jenis ?? '-') }}
                                        @else
                                            -
                                        @endif
                                    </td>

                                    <td class="px-4 py-2 flex gap-2">
                                        <a href="{{ route('users.edit', $user->id) }}"
                                        class="inline-flex items-center gap-1 px-3 py-1 bg-yellow-500 text-white text-xs rounded hover:bg-yellow-600 shadow">
                                            <i class="bi bi-pencil-fill"></i> Edit
                                        </a>
                                        <form method="POST" action="{{ route('users.destroy', $user->id) }}" class="delete-form">
                                            @csrf
                                            @method('DELETE')
                                            <button type="button"
                                                    class="delete-button inline-flex items-center gap-1 px-3 py-1 bg-red-600 text-white text-xs rounded hover:bg-red-700 shadow">
                                                <i class="bi bi-trash-fill"></i> Hapus
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                {{-- SweetAlert Script --}}
                <script>
                    document.querySelectorAll('.delete-button').forEach(button => {
                        button.addEventListener('click', function() {
                            Swal.fire({
                                title: 'Yakin mau hapus user ini?',
                                icon: 'warning',
                                showCancelButton: true,
                                confirmButtonColor: '#d33',
                                cancelButtonColor: '#6c757d',
                                confirmButtonText: 'Ya, hapus!',
                                cancelButtonText: 'Batal'
                            }).then((result) => {
                                if (result.isConfirmed) {
                                    this.closest('form').submit();
                                }
                            });
                        });
                    });
                </script>
            </div>
        </div>
    </div>
</x-app-layout>
