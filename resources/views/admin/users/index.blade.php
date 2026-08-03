<x-app-shell>
<h2 class="font-semibold text-xl text-[var(--sabira-ink)]">Manajemen User</h2>

    <div class="flex">
        <div class="mt-6 w-full sm:px-6 lg:px-8 space-y-6">
            <div class="bg-[var(--sabira-surface)] shadow-md rounded-2xl p-6">
                {{-- Tombol Tambah User --}}
                <div class="mb-4">
                    <a href="{{ route('users.create') }}"
                       class="inline-flex items-center gap-2 bg-[var(--sabira-primary)] text-white px-4 py-2 rounded-md hover:bg-[var(--sabira-primary-active)] shadow">
                        <i class="bi bi-person-plus-fill"></i> Tambah User
                    </a>
                </div>

                {{-- Tabel User --}}
                <div class="overflow-x-auto">
                    <table id="users-table" class="w-full table-auto text-left text-sm text-[var(--sabira-body)]">
                        <thead class="bg-[var(--sabira-neutral-strong)] text-white uppercase text-xs font-semibold">
                            <tr>
                                <th class="px-4 py-3">Nama</th>
                                <th class="px-4 py-3">Role</th>
                                <th class="px-4 py-3">Email</th>
                                <th class="px-4 py-3">Status</th>
                                <th class="px-4 py-3">Divisi/Jenis</th>
                                <th class="px-4 py-3">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-[#D6D8D2]">
                            @foreach($users as $user)
                                <tr class="hover:bg-[var(--sabira-surface-strong)] transition">
                                    <td class="px-4 py-2">{{ $user->name }}</td>
                                    <td class="px-4 py-2 capitalize">{{ $user->role }}</td>
                                    <td class="px-4 py-2">{{ $user->email }}</td>
                                    <td class="px-4 py-2 capitalize {{ $user->status == 'aktif' ? 'text-green-600' : 'text-red-600' }}">
                                        {{ $user->status }}
                                    </td>
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
                                        <button data-modal-target="userDetailModal-{{ $user->id }}"
                                                data-modal-toggle="userDetailModal-{{ $user->id }}"
                                                class="inline-flex items-center gap-1 px-3 py-1 bg-[var(--sabira-primary)] text-white text-xs rounded hover:bg-sky-700 shadow">
                                            <i class="bi bi-eye-fill"></i> Lihat
                                        </button>

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
                            @foreach($users as $user)
                                <!-- Modal Detail User -->
                                <div id="userDetailModal-{{ $user->id }}" tabindex="-1" aria-hidden="true"
                                    class="fixed top-0 left-0 right-0 z-50 hidden w-full p-4 overflow-x-hidden overflow-y-auto h-[calc(100%-1rem)] max-h-full">
                                    <div class="relative w-full max-w-md max-h-full">
                                        <div class="relative bg-[var(--sabira-surface)] rounded-xl shadow-xl text-[var(--sabira-ink)]">
                                            <div class="flex items-start justify-between p-4 border-b border-[#8D9382] rounded-t">
                                                <h3 class="text-xl font-semibold">Detail User</h3>
                                                <button type="button"
                                                    class="text-gray-500 bg-transparent hover:bg-gray-300 hover:text-gray-900 rounded-lg text-sm p-1.5 ml-auto inline-flex items-center"
                                                    data-modal-hide="userDetailModal-{{ $user->id }}">
                                                    <svg aria-hidden="true" class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"
                                                        xmlns="http://www.w3.org/2000/svg">
                                                        <path fill-rule="evenodd"
                                                            d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 011.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z"
                                                            clip-rule="evenodd"></path>
                                                    </svg>
                                                </button>
                                            </div>

                                            <div class="p-6 space-y-4 text-sm">
                                                <div class="flex justify-center mb-4">
                                                    <img src="{{ $user->karyawan?->foto
                                                        ? asset('storage/' . $user->karyawan->foto)
                                                        : asset('images/default-photo.jpg') }}"
                                                        alt="Foto"
                                                        class="w-20 h-24 object-cover rounded shadow">
                                                </div>

                                                <p><strong>Nama:</strong> {{ $user->name }}</p>
                                                <p><strong>Email:</strong> {{ $user->email }}</p>
                                                <p><strong>Role:</strong> {{ ucfirst($user->role) }}</p>
                                                <p><strong>Status:</strong>
                                                    <span class="{{ $user->status == 'aktif' ? 'text-green-600' : 'text-red-600' }}">
                                                        {{ ucfirst($user->status) }}
                                                    </span>
                                                </p>

                                                @if($user->role === 'karyawan')
                                                    <p><strong>Divisi:</strong> {{ $user->karyawan->divisi->nama ?? '-' }}</p>
                                                    <p><strong>No HP:</strong> {{ $user->karyawan->no_hp ?? '-' }}</p>
                                                    <p><strong>Alamat:</strong> {{ $user->karyawan->alamat ?? '-' }}</p>
                                                @elseif($user->role === 'guru')
                                                    <p><strong>Jenis Guru:</strong> {{ ucfirst($user->guru->jenis ?? '-') }}</p>
                                                    <p><strong>No HP:</strong> {{ $user->karyawan->no_hp ?? '-' }}</p>
                                                    <p><strong>Alamat:</strong> {{ $user->karyawan->alamat ?? '-' }}</p>
                                                @endif
                                            </div>

                                            <div class="flex justify-end p-4 border-t border-[#8D9382] rounded-b">
                                                <button data-modal-hide="userDetailModal-{{ $user->id }}" type="button"
                                                    class="text-white bg-[var(--sabira-primary)] hover:bg-[var(--sabira-primary-active)] focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm px-4 py-2">
                                                    Tutup
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <script>
                    document.addEventListener('DOMContentLoaded', () => {
                        document.querySelectorAll('.delete-button').forEach((button) => {
                            button.addEventListener('click', () => {
                                if (confirm('Hapus pengguna ini? Data yang dihapus tidak dapat dikembalikan.')) {
                                    button.closest('form')?.requestSubmit();
                                }
                            });
                        });
                    });
                </script>
            </div>
        </div>
    </div>
</x-app-shell>
