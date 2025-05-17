<x-app-layout>
    <div class="flex">
        <x-admin-sidenav />
        <div class="mt-6 w-full sm:px-6 lg:px-8 space-y-6">
            <div class="mb-4">
                <a href="{{ route('users.index') }}" class="inline-flex items-center text-sm text-gray-700 hover:text-blue-600">
                    <i class="bi bi-arrow-left-circle-fill text-lg mr-1"></i>
                </a>
            </div>

            <div class="flex-1 p-2">
                <div class="bg-white dark:bg-gray-800 shadow rounded-xl p-6 max-h-[calc(100vh-100px)] overflow-y-auto">
                    <h1 class="text-2xl font-bold text-gray-800 dark:text-white mb-4">Edit User</h1>
                    <form action="{{ route('users.update', $user->id) }}" method="POST" enctype="multipart/form-data" class="space-y-4">
                        @csrf
                        @method('PUT')

                        <!-- Nama -->
                        <div>
                            <label class="block text-white ">Nama</label>
                            <input type="text" name="name" class="w-full rounded border-gray-300 @error('name') border-red-500 @enderror"
                                value="{{ old('name', $user->name) }}" required>
                            @error('name') <p class="text-red-500 text-sm mt-1">{{ $message }}</p> @enderror
                        </div>

                        <!-- Email -->
                        <div>
                            <label class="block text-white">Email</label>
                            <input type="email" name="email" class="w-full rounded border-gray-300 @error('email') border-red-500 @enderror"
                                value="{{ old('email', $user->email) }}" required>
                            @error('email') <p class="text-red-500 text-sm mt-1">{{ $message }}</p> @enderror
                        </div>

                        <!-- Role -->
                        <div>
                            <label class="block text-white">Role</label>
                            <select name="role" id="roleSelect" class="w-full rounded border-gray-300" required>
                                <option value="admin" {{ $user->role == 'admin' ? 'selected' : '' }}>Admin</option>
                                <option value="karyawan" {{ $user->role == 'karyawan' ? 'selected' : '' }}>Karyawan</option>
                                <option value="guru" {{ $user->role == 'guru' ? 'selected' : '' }}>Guru</option>
                            </select>
                            @error('role') <p class="text-red-500 text-sm mt-1">{{ $message }}</p> @enderror
                        </div>

                        <!-- Password -->
                        <div>
                            <label class="block text-white">Password (Biarkan kosong jika tidak diubah)</label>
                            <input type="password" name="password" class="w-full rounded border-gray-300 @error('password') border-red-500 @enderror">
                            @error('password') <p class="text-red-500 text-sm mt-1">{{ $message }}</p> @enderror
                        </div>

                        <!-- Karyawan Fields -->
                        <div id="karyawanFields" style="{{ $user->role === 'karyawan' ? '' : 'display: none;' }}" class="border-t pt-4 space-y-4">
                            <h3 class="text-lg font-semibold text-gray-700 dark:text-gray-300">Data Karyawan</h3>

                            <div>
                                <label class="block text-white">Nama Lengkap</label>
                                <input type="text" name="nama_lengkap" class="w-full rounded border-gray-300"
                                    value="{{ old('nama_lengkap', $user->karyawan->nama_lengkap ?? '') }}">
                            </div>

                            <div>
                                <label class="block text-white">Divisi</label>
                                <select name="divisi_id" class="w-full rounded border-gray-300">
                                    <option value="">-- Pilih Divisi --</option>
                                    @foreach($divisis as $divisi)
                                        <option value="{{ $divisi->id }}" {{ (old('divisi_id', $user->karyawan->divisi_id ?? '') == $divisi->id) ? 'selected' : '' }}>
                                            {{ $divisi->nama }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div>
                                <label class="block text-white">Alamat</label>
                                <textarea name="alamat" class="w-full rounded border-gray-300">{{ old('alamat', $user->karyawan->alamat ?? '') }}</textarea>
                            </div>

                            <div>
                                <label class="block text-white">No HP</label>
                                <input type="text" name="no_hp" class="w-full rounded border-gray-300"
                                    value="{{ old('no_hp', $user->karyawan->no_hp ?? '') }}">
                            </div>

                            <div>
                                <label class="block text-white">Foto (Biarkan kosong jika tidak diubah)</label>
                                <input type="file" name="foto" class="w-full rounded border-gray-300">
                            </div>
                        </div>

                        <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">
                            Update
                        </button>
                    </form>

                    <script>
                        const role = document.getElementById('roleSelect');
                        const karyawanFields = document.getElementById('karyawanFields');

                        function toggleKaryawanFields() {
                            karyawanFields.style.display = role.value === 'karyawan' ? 'block text-white' : 'none';
                        }

                        role.addEventListener('change', toggleKaryawanFields);
                        window.addEventListener('DOMContentLoaded', toggleKaryawanFields);
                    </script>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
