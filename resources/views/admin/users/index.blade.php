<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200">Manajemen User</h2>
    </x-slot>
    <div class="flex">
        <x-admin-sidenav />

        <div class="mt-6 w-full sm:px-6 lg:px-8 space-y-6">
            <div class="bg-white dark:bg-gray-800 shadow rounded-xl p-6">
                <a href="{{ route('users.create') }}"
                class="mb-4 inline-block bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">
                    + Tambah User
                </a>
                <table class="w-full table-auto text-left text-sm text-gray-500 dark:text-gray-400">
                    <thead class="bg-gray-100 dark:bg-gray-700">
                        <tr>
                            <th class="px-4 py-2">Nama</th>
                            <th class="px-4 py-2">Role</th>
                            <th class="px-4 py-2">Email</th>
                            {{-- <th class="px-4 py-2 text-left">Password (hash)</th> --}}
                            <th class="px-4 py-2">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($users as $user)
                            <tr class="border-b">
                                <td class="px-4 py-2">{{ $user->name }}</td>
                                <td class="px-4 py-2">{{ $user->role }}</td>
                                <td class="px-4 py-2">{{ $user->email }}</td>
                                {{-- <td class="px-4 py-2 text-xs text-gray-500 truncate">{{ $user->password }}</td> --}}
                                <td class="px-4 py-2 flex items-center gap-2">
                                    <a href="{{ route('users.edit', $user->id) }}"
                                    class="px-3 py-1 bg-yellow-500 text-white text-xs rounded hover:bg-yellow-600">
                                        Edit
                                    </a>
                                    <form method="POST" action="{{ route('users.destroy', $user->id) }}" class="delete-form">
                                        @csrf
                                        @method('DELETE')
                                        <button type="button" class="delete-button px-3 py-1 bg-red-600 text-white text-xs rounded hover:bg-red-700">
                                            Delete
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{-- SweetAlert Success Notification --}}
            <script>
                document.querySelectorAll('.delete-button').forEach(button => {
                    button.addEventListener('click', function() {
                        Swal.fire({
                            title: 'Yakin mau hapus user ini?',
                            icon: 'warning',
                            showCancelButton: true,
                            confirmButtonColor: '#d33',
                            cancelButtonColor: '#3085d6',
                            confirmButtonText: 'Ya, hapus!',
                            cancelButtonText: 'Batal'
                        }).then((result) => {
                            if (result.isConfirmed) {
                                // Submit form terdekat
                                this.closest('form').submit();
                            }
                        });
                    });
                });
            </script>
        </div>
    </div>
</x-app-layout>

