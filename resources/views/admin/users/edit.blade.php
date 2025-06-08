<x-app-layout>
    <div class="flex">
        <x-admin-sidenav />

        <div class="mt-6 w-full sm:px-6 lg:px-8 space-y-6">
            <div class="mb-4">
                <a href="{{ route('users.index') }}" class="inline-flex items-center text-sm text-gray-700 hover:text-blue-600">
                    <i class="bi bi-arrow-left-circle-fill text-lg mr-1"></i>
                    Kembali
                </a>
            </div>

            <div class="bg-white dark:bg-gray-800 shadow rounded-xl p-6 max-h-[calc(100vh-100px)] overflow-y-auto">
                <h1 class="text-2xl font-bold text-gray-800 dark:text-white mb-4">Edit User</h1>

                <form action="{{ route('users.update', $user->id) }}" method="POST" enctype="multipart/form-data" class="space-y-4 w-full">
                    @csrf
                    @method('PUT')

                    <!-- Nama -->
                    <div>
                        <label class="block text-gray-800 dark:text-gray-200 mb-1">Nama</label>
                        <input type="text" name="name" class="w-full rounded border-gray-300 @error('name') border-red-500 @enderror"
                            value="{{ old('name', $user->name) }}" required>
                        @error('name') <p class="text-red-500 text-sm mt-1">{{ $message }}</p> @enderror
                    </div>

                    <!-- Email -->
                    <div>
                        <label class="block text-gray-800 dark:text-gray-200">Email</label>
                        <input type="email" name="email" class="w-full rounded border-gray-300 @error('email') border-red-500 @enderror"
                            value="{{ old('email', $user->email) }}" required>
                        @error('email') <p class="text-red-500 text-sm mt-1">{{ $message }}</p> @enderror
                    </div>

                    <!-- Role -->
                    <div>
                        <label class="block text-gray-800 dark:text-gray-200">Role</label>
                        <select name="role" id="roleSelect" class="w-full rounded border-gray-300 @error('role') border-red-500 @enderror" required>
                            <option value="">-- Pilih Role --</option>
                            <option value="admin" {{ old('role', $user->role) == 'admin' ? 'selected' : '' }}>Admin</option>
                            <option value="karyawan" {{ old('role', $user->role) == 'karyawan' ? 'selected' : '' }}>Karyawan</option>
                            <option value="guru" {{ old('role', $user->role) == 'guru' ? 'selected' : '' }}>Guru</option>
                        </select>
                        @error('role') <p class="text-red-500 text-sm mt-1">{{ $message }}</p> @enderror
                    </div>

                    <!-- Password -->
                    <div>
                        <label class="block text-gray-800 dark:text-gray-200">Password (Kosongkan jika tidak diubah)</label>
                        <input type="password" name="password" class="w-full rounded border-gray-300 @error('password') border-red-500 @enderror">
                        @error('password') <p class="text-red-500 text-sm mt-1">{{ $message }}</p> @enderror
                    </div>

                    <!-- Karyawan / Guru Fields -->
                    <div id="karyawanFields" style="display: none;" class="border-t pt-4 space-y-4">
                        <h3 class="text-lg font-semibold text-gray-700 dark:text-gray-300">Data Karyawan</h3>

                        <div>
                            <label class="block text-gray-800 dark:text-gray-200">Nama Lengkap</label>
                            <input type="text" name="nama_lengkap"
                                value="{{ old('nama_lengkap', $user->karyawan->nama_lengkap ?? '') }}"
                                class="w-full rounded border-gray-300 @error('nama_lengkap') border-red-500 @enderror">
                            @error('nama_lengkap') <p class="text-red-500 text-sm mt-1">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="block text-gray-800 dark:text-gray-200">Divisi</label>
                            <select name="divisi_id"
                                class="w-full rounded border-gray-300 @error('divisi_id') border-red-500 @enderror">
                                <option value="">-- Pilih Divisi --</option>
                                @foreach($divisis as $divisi)
                                    <option value="{{ $divisi->id }}"
                                        {{ old('divisi_id', $user->karyawan->divisi_id ?? '') == $divisi->id ? 'selected' : '' }}>
                                        {{ $divisi->nama }}
                                    </option>
                                @endforeach
                            </select>
                            @error('divisi_id') <p class="text-red-500 text-sm mt-1">{{ $message }}</p> @enderror
                        </div>

                        <!-- Jenis Guru: hanya tampil jika role guru -->
                        <div id="jenisGuruField" style="display: none;">
                            <label class="block text-gray-800 dark:text-gray-200">Jenis Guru</label>
                            <select name="jenis" class="w-full rounded border-gray-300 @error('jenis') border-red-500 @enderror">
                                <option value="">-- Pilih Jenis --</option>
                                <option value="akademik" {{ old('jenis', optional($user->guru)->jenis) == 'akademik' ? 'selected' : '' }}>Akademik</option>
                                <option value="muadalah" {{ old('jenis', optional($user->guru)->jenis) == 'muadalah' ? 'selected' : '' }}>Muadalah</option>
                                <option value="asrama" {{ old('jenis', optional($user->guru)->jenis) == 'asrama' ? 'selected' : '' }}>Asrama</option>
                            </select>
                            @error('jenis') <p class="text-red-500 text-sm mt-1">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="block text-gray-800 dark:text-gray-200">Alamat</label>
                            <textarea name="alamat"
                                class="w-full rounded border-gray-300 @error('alamat') border-red-500 @enderror">{{ old('alamat', $user->karyawan->alamat ?? '') }}</textarea>
                            @error('alamat') <p class="text-red-500 text-sm mt-1">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="block text-gray-800 dark:text-gray-200">No HP</label>
                            <input type="text" name="no_hp"
                                value="{{ old('no_hp', $user->karyawan->no_hp ?? '') }}"
                                class="w-full rounded border-gray-300 @error('no_hp') border-red-500 @enderror">
                            @error('no_hp') <p class="text-red-500 text-sm mt-1">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="block text-gray-800 dark:text-gray-200">Foto (Kosongkan jika tidak diubah)</label>
                            <input type="file" name="foto" class="w-full rounded border-gray-300 @error('foto') border-red-500 @enderror">
                            @error('foto') <p class="text-red-500 text-sm mt-1">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">
                        Update
                    </button>
                </form>
            </div>
        </div>
    </div>

    <script>
        const roleSelect = document.getElementById('roleSelect');
        const karyawanFields = document.getElementById('karyawanFields');
        const jenisGuruField = document.getElementById('jenisGuruField');
        const inputs = karyawanFields.querySelectorAll('input, select, textarea');
        const jenisSelect = document.querySelector('select[name="jenis"]');

        function toggleFields() {
            const role = roleSelect.value;
            const showKaryawan = role === 'karyawan' || role === 'guru';
            karyawanFields.style.display = showKaryawan ? 'block' : 'none';

            inputs.forEach(input => {
                // Foto tidak required
                if(input.name !== 'foto'){
                    input.required = showKaryawan;
                } else {
                    input.required = false;
                }
            });

            // Handle required untuk select jenis guru
            if (role === 'guru') {
                jenisGuruField.style.display = 'block';
                if (jenisSelect) jenisSelect.required = true;

                // Set divisi otomatis ke divisi 'guru' jika ada
                const divisiSelect = document.querySelector('select[name="divisi_id"]');
                if(divisiSelect){
                    let guruDivisiOption = Array.from(divisiSelect.options).find(opt => opt.text.toLowerCase() === 'guru');
                    if(guruDivisiOption){
                        divisiSelect.value = guruDivisiOption.value;
                    }
                }
            } else {
                jenisGuruField.style.display = 'none';
                if (jenisSelect) jenisSelect.required = false;
            }
        }

        roleSelect.addEventListener('change', toggleFields);
        window.addEventListener('DOMContentLoaded', toggleFields);
    </script>

</x-app-layout>
