<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200">
            Tambah User Baru
        </h2>
    </x-slot>

    <div class="py-6 max-w-4xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white dark:bg-gray-800 shadow rounded-xl p-6">
            <form action="{{ route('users.store') }}" method="POST" enctype="multipart/form-data" class="space-y-4">
                @csrf

                {{-- USER --}}
                <div>
                    <label class="block">Nama</label>
                    <input type="text" name="name"
                        class="w-full rounded border-gray-300 @error('name') border-red-500 @enderror"
                        value="{{ old('name') }}" required>
                    @error('name') <p class="text-red-500 text-sm mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block">Email</label>
                    <input type="email" name="email"
                        class="w-full rounded border-gray-300 @error('email') border-red-500 @enderror"
                        value="{{ old('email') }}" required>
                    @error('email') <p class="text-red-500 text-sm mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block">Role</label>
                    <select name="role" id="roleSelect"
                        class="w-full rounded border-gray-300 @error('role') border-red-500 @enderror" required>
                        <option value="">-- Pilih Role --</option>
                        <option value="admin" {{ old('role') == 'admin' ? 'selected' : '' }}>Admin</option>
                        <option value="karyawan" {{ old('role') == 'karyawan' ? 'selected' : '' }}>Karyawan</option>
                        <option value="guru" {{ old('role') == 'guru' ? 'selected' : '' }}>Guru</option>
                    </select>
                    @error('role') <p class="text-red-500 text-sm mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block">Password</label>
                    <input type="password" name="password"
                        class="w-full rounded border-gray-300 @error('password') border-red-500 @enderror" required>
                    @error('password') <p class="text-red-500 text-sm mt-1">{{ $message }}</p> @enderror
                </div>

                {{-- KHUSUS KARYAWAN --}}
                <div id="karyawanFields" style="display: none;" class="border-t pt-4 space-y-4">
                    <h3 class="text-lg font-semibold text-gray-700 dark:text-gray-300">Data Karyawan</h3>

                    <div>
                        <label class="block">Nama Lengkap</label>
                        <input type="text" name="nama_lengkap"
                            class="w-full rounded border-gray-300 @error('nama_lengkap') border-red-500 @enderror"
                            value="{{ old('nama_lengkap') }}">
                        @error('nama_lengkap') <p class="text-red-500 text-sm mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block">Divisi</label>
                        <select name="divisi_id"
                            class="w-full rounded border-gray-300 @error('divisi_id') border-red-500 @enderror">
                            <option value="">-- Pilih Divisi --</option>
                            @foreach($divisis as $divisi)
                                <option value="{{ $divisi->id }}" {{ old('divisi_id') == $divisi->id ? 'selected' : '' }}>
                                    {{ $divisi->nama }}
                                </option>
                            @endforeach
                        </select>
                        @error('divisi_id') <p class="text-red-500 text-sm mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block">Alamat</label>
                        <textarea name="alamat"
                            class="w-full rounded border-gray-300 @error('alamat') border-red-500 @enderror">{{ old('alamat') }}</textarea>
                        @error('alamat') <p class="text-red-500 text-sm mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block">No HP</label>
                        <input type="text" name="no_hp"
                            class="w-full rounded border-gray-300 @error('no_hp') border-red-500 @enderror"
                            value="{{ old('no_hp') }}">
                        @error('no_hp') <p class="text-red-500 text-sm mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block">Foto</label>
                        <input type="file" name="foto"
                            class="w-full rounded border-gray-300 @error('foto') border-red-500 @enderror">
                        @error('foto') <p class="text-red-500 text-sm mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>

                <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">
                    Simpan
                </button>
            </form>
        </div>
    </div>

    <script>
        const role = document.getElementById('roleSelect');
        const karyawanFields = document.getElementById('karyawanFields');

        function toggleKaryawanFields() {
            const isKaryawan = role.value === 'karyawan';
            karyawanFields.style.display = isKaryawan ? 'block' : 'none';

            // Jangan disable, cukup atur required
            const inputs = karyawanFields.querySelectorAll('input, select, textarea');
            inputs.forEach(input => {
                input.required = isKaryawan;
            });
        }

        role.addEventListener('change', toggleKaryawanFields);
        window.addEventListener('DOMContentLoaded', toggleKaryawanFields);
    </script>
</x-app-layout>
