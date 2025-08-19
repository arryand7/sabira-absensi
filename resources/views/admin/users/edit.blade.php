<x-app-layout>
    <x-slot name="sidebar">
        <x-admin-sidenav />
    </x-slot>

    <div class="flex">
        <div class="mt-6 w-full sm:px-6 lg:px-8 space-y-6">
            <div class="mb-4">
                <button onclick="window.history.back();"
                    class="bg-gray-300 hover:bg-gray-400 text-gray-800 px-4 py-2 rounded-md shadow flex items-center gap-2">
                    <i class="bi bi-arrow-left-circle-fill"></i> Kembali
                </button>
            </div>

            <div class="bg-[#8D9382] shadow rounded-xl p-6 max-h-[calc(100vh-100px)] overflow-y-auto">
                <h1 class="text-2xl font-bold text-[#1C1E17] mb-4">Edit User</h1>

                <form action="{{ route('users.update', $user->id) }}" method="POST" enctype="multipart/form-data" class="space-y-4 w-full">
                    @csrf
                    @method('PUT')

                    <!-- Nama -->
                    <div>
                        <label class="block text-[#1C1E17] mb-1">Nama</label>
                        <input type="text" name="name"
                            class="w-full rounded border-gray-300 bg-[#EEF3E9] text-[#1C1E17] @error('name') border-red-500 @enderror"
                            value="{{ old('name', $user->name) }}" required>
                        @error('name') <p class="text-red-500 text-sm mt-1">{{ $message }}</p> @enderror
                    </div>

                    <!-- Email -->
                    <div>
                        <label class="block text-[#1C1E17]">Email</label>
                        <input type="email" name="email"
                            class="w-full rounded border-gray-300 bg-[#EEF3E9] text-[#1C1E17] @error('email') border-red-500 @enderror"
                            value="{{ old('email', $user->email) }}" required>
                        @error('email') <p class="text-red-500 text-sm mt-1">{{ $message }}</p> @enderror
                    </div>

                    <!-- Role -->
                    <div>
                        <label class="block text-[#1C1E17]">Role</label>
                        <select name="role" id="roleSelect"
                            class="w-full rounded border-gray-300 bg-[#EEF3E9] text-[#1C1E17] @error('role') border-red-500 @enderror"
                            required>
                            <option value="">-- Pilih Role --</option>
                            <option value="admin" {{ old('role', $user->role) == 'admin' ? 'selected' : '' }}>Admin</option>
                            <option value="karyawan" {{ old('role', $user->role) == 'karyawan' ? 'selected' : '' }}>Karyawan</option>
                            <option value="guru" {{ old('role', $user->role) == 'guru' ? 'selected' : '' }}>Guru</option>
                            <option value="organisasi" {{ old('role', $user->role) == 'organisasi' ? 'selected' : '' }}>Organisasi</option>
                        </select>
                        @error('role') <p class="text-red-500 text-sm mt-1">{{ $message }}</p> @enderror
                    </div>

                    <!-- Password -->
                    <div>
                        <label class="block text-[#1C1E17]">Password (Kosongkan jika tidak diubah)</label>
                        <input type="password" name="password"
                            class="w-full rounded border-gray-300 bg-[#EEF3E9] text-[#1C1E17] @error('password') border-red-500 @enderror">
                        @error('password') <p class="text-red-500 text-sm mt-1">{{ $message }}</p> @enderror
                    </div>

                    <!-- Status -->
                    <div>
                        <label class="block text-[#1C1E17] mb-1">Status</label>
                        <label class="inline-flex items-center cursor-pointer">
                            <input type="hidden" name="status" value="nonaktif">
                            <input type="checkbox" id="statusCheckbox" name="status" value="aktif"
                                {{ old('status', $user->status) === 'aktif' ? 'checked' : '' }}>
                            <span class="ml-3 text-sm text-gray-700">Aktif</span>
                        </label>
                    </div>

                    <!-- Data Karyawan -->
                    <div id="karyawanFields" style="display: none;" class="border-t pt-4 space-y-4">
                        <h3 class="text-lg font-semibold text-[#1C1E17]">Data Karyawan</h3>

                        <!-- Divisi -->
                        <div id="divisiField">
                            <label class="block text-[#1C1E17]">Divisi</label>
                            <select name="divisi_id"
                                class="w-full rounded border-gray-300 bg-[#EEF3E9] text-[#1C1E17] @error('divisi_id') border-red-500 @enderror">
                                <option value="">-- Pilih Divisi --</option>
                                @foreach($divisis as $divisi)
                                    <option value="{{ $divisi->id }}" {{ old('divisi_id', $user->karyawan->divisi_id ?? '') == $divisi->id ? 'selected' : '' }}>
                                        {{ $divisi->nama }}
                                    </option>
                                @endforeach
                            </select>
                            @error('divisi_id') <p class="text-red-500 text-sm mt-1">{{ $message }}</p> @enderror
                        </div>

                        <!-- Jenis Guru -->
                        <div id="jenisGuruField" style="display: none;">
                            <label class="block text-[#1C1E17]">Jenis Guru</label>
                            <select name="jenis"
                                class="w-full rounded border-gray-300 bg-[#EEF3E9] text-[#1C1E17] @error('jenis') border-red-500 @enderror">
                                <option value="">-- Pilih Jenis Guru --</option>
                                <option value="formal" {{ old('jenis', optional($user->guru)->jenis) == 'formal' ? 'selected' : '' }}>Formal</option>
                                <option value="muadalah" {{ old('jenis', optional($user->guru)->jenis) == 'muadalah' ? 'selected' : '' }}>Muadalah</option>
                            </select>
                            @error('jenis') <p class="text-red-500 text-sm mt-1">{{ $message }}</p> @enderror
                        </div>

                        <!-- Alamat -->
                        <div>
                            <label class="block text-[#1C1E17]">Alamat</label>
                            <textarea name="alamat"
                                class="w-full rounded border-gray-300 bg-[#EEF3E9] text-[#1C1E17] @error('alamat') border-red-500 @enderror">{{ old('alamat', $user->karyawan->alamat ?? '') }}</textarea>
                            @error('alamat') <p class="text-red-500 text-sm mt-1">{{ $message }}</p> @enderror
                        </div>

                        <!-- No HP -->
                        <div>
                            <label class="block text-[#1C1E17]">No HP</label>
                            <input type="text" name="no_hp"
                                value="{{ old('no_hp', $user->karyawan->no_hp ?? '') }}"
                                class="w-full rounded border-gray-300 bg-[#EEF3E9] text-[#1C1E17] @error('no_hp') border-red-500 @enderror">
                            @error('no_hp') <p class="text-red-500 text-sm mt-1">{{ $message }}</p> @enderror
                        </div>

                        <!-- Foto -->
                        <div>
                            <label class="block text-[#1C1E17]">Foto (Kosongkan jika tidak diubah)</label>
                            <input type="file" name="foto"
                                class="w-full rounded border-gray-300 bg-[#EEF3E9] text-[#1C1E17] @error('foto') border-red-500 @enderror">
                            @error('foto') <p class="text-red-500 text-sm mt-1">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    <!-- Submit -->
                    <button type="submit" class="bg-[#8E412E] text-white px-4 py-2 rounded-md text-xs hover:bg-[#BA6F4D] shadow">
                        <i class="bi bi-save mr-1"></i> Update
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- SCRIPT -->
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const roleSelect = document.getElementById('roleSelect');
            const karyawanFields = document.getElementById('karyawanFields');
            const jenisGuruField = document.getElementById('jenisGuruField');
            const divisiField = document.getElementById('divisiField');

            function toggleFields() {
                const role = roleSelect.value;
                const isKaryawan = role === 'karyawan';
                const isGuru = role === 'guru';

                karyawanFields.style.display = (isKaryawan || isGuru) ? 'block' : 'none';
                jenisGuruField.style.display = isGuru ? 'block' : 'none';
                divisiField.style.display = isGuru ? 'none' : 'block';
            }

            roleSelect.addEventListener('change', toggleFields);
            toggleFields(); // run on load
        });
    </script>
</x-app-layout>
