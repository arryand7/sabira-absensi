<x-app-layout>
    <x-slot name="sidebar">
        <x-admin-sidenav />
    </x-slot>

    <div class="flex">
        <div class="mt-6 w-full sm:px-6 lg:px-8 space-y-6">
            <div class="mb-4">
                <a href="{{ route('users.index') }}" class="inline-flex items-center text-sm text-[#1C1E17] hover:text-blue-600">
                    <i class="bi bi-arrow-left-circle-fill text-lg mr-1"></i>
                </a>
            </div>

            <div class="bg-[#8D9382] shadow rounded-xl p-6 max-h-[calc(100vh-100px)] overflow-y-auto">
                <h1 class="text-2xl font-bold text-[#1C1E17] mb-4">Create User</h1>

                <form action="{{ route('users.store') }}" method="POST" enctype="multipart/form-data" class="space-y-4">
                    @csrf

                    {{-- USER --}}
                    <div>
                        <label class="block text-[#1C1E17]">Nama</label>
                        <input type="text" name="name" class="w-full rounded bg-[#EEF3E9] border-gray-300 text-[#1C1E17] @error('name') border-red-500 @enderror"
                            value="{{ old('name') }}" required>
                        @error('name') <p class="text-red-500 text-sm mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-[#1C1E17]">Email</label>
                        <input type="email" name="email" class="w-full rounded bg-[#EEF3E9] border-gray-300 text-[#1C1E17] @error('email') border-red-500 @enderror"
                            value="{{ old('email') }}" required>
                        @error('email') <p class="text-red-500 text-sm mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-[#1C1E17]">Role</label>
                        <select name="role" id="roleSelect"
                            class="w-full rounded bg-[#EEF3E9] border-gray-300 text-[#1C1E17] @error('role') border-red-500 @enderror" required>
                            <option value="">-- Pilih Role --</option>
                            <option value="admin" {{ old('role') == 'admin' ? 'selected' : '' }}>Admin</option>
                            <option value="karyawan" {{ old('role') == 'karyawan' ? 'selected' : '' }}>Karyawan</option>
                            <option value="guru" {{ old('role') == 'guru' ? 'selected' : '' }}>Guru</option>
                            <option value="organisasi" {{ old('role') == 'organisasi' ? 'selected' : '' }}>Organisasi</option> <!-- Tambahan -->
                        </select>
                        @error('role') <p class="text-red-500 text-sm mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-[#1C1E17]">Password</label>
                        <input type="password" name="password"
                            class="w-full rounded bg-[#EEF3E9] border-gray-300 text-[#1C1E17] @error('password') border-red-500 @enderror" required>
                        @error('password') <p class="text-red-500 text-sm mt-1">{{ $message }}</p> @enderror
                    </div>

                    {{-- FORM KARYAWAN --}}
                    <div id="karyawanFields" style="display: none;" class="border-t pt-4 space-y-4">
                        <h3 class="text-lg font-semibold text-[#1C1E17]">Data Karyawan</h3>

                        <div>
                            <label class="block text-[#1C1E17]">Nama Lengkap</label>
                            <input type="text" name="nama_lengkap"
                                class="w-full rounded bg-[#EEF3E9] border-gray-300 text-[#1C1E17] @error('nama_lengkap') border-red-500 @enderror"
                                value="{{ old('nama_lengkap') }}">
                            @error('nama_lengkap') <p class="text-red-500 text-sm mt-1">{{ $message }}</p> @enderror
                        </div>

                        <div id="divisiField">
                            <label class="block text-[#1C1E17]">Divisi</label>
                            <select name="divisi_id"
                                class="w-full rounded bg-[#EEF3E9] border-gray-300 text-[#1C1E17] @error('divisi_id') border-red-500 @enderror">
                                <option value="">-- Pilih Divisi --</option>
                                @foreach($divisis as $divisi)
                                    <option value="{{ $divisi->id }}" data-nama="{{ strtolower($divisi->nama) }}"
                                        {{ old('divisi_id') == $divisi->id ? 'selected' : '' }}>
                                        {{ $divisi->nama }}
                                    </option>
                                @endforeach
                            </select>
                            @error('divisi_id') <p class="text-red-500 text-sm mt-1">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="block text-[#1C1E17]">Alamat</label>
                            <textarea name="alamat"
                                class="w-full rounded bg-[#EEF3E9] border-gray-300 text-[#1C1E17] @error('alamat') border-red-500 @enderror">{{ old('alamat') }}</textarea>
                            @error('alamat') <p class="text-red-500 text-sm mt-1">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="block text-[#1C1E17]">No HP</label>
                            <input type="text" name="no_hp"
                                class="w-full rounded bg-[#EEF3E9] border-gray-300 text-[#1C1E17] @error('no_hp') border-red-500 @enderror"
                                value="{{ old('no_hp') }}">
                            @error('no_hp') <p class="text-red-500 text-sm mt-1">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="block text-[#1C1E17]">Foto</label>
                            <input type="file" name="foto"
                                class="w-full rounded bg-[#EEF3E9] border-gray-300 text-[#1C1E17] @error('foto') border-red-500 @enderror">
                            @error('foto') <p class="text-red-500 text-sm mt-1">{{ $message }}</p> @enderror
                        </div>

                        {{-- Tambahan jika role == guru --}}
                        <div id="guruFields" style="display: none;">
                            <label class="block text-[#1C1E17]">Jenis Guru</label>
                            <div class="space-y-2">
                                <label class="block text-[#1C1E17]">Jenis Guru</label>
                                <div class="flex items-center space-x-4">
                                    <label class="flex items-center space-x-2">
                                        <input type="checkbox" name="jenis_guru[]" value="akademik"
                                            {{ is_array(old('jenis_guru')) && in_array('akademik', old('jenis_guru')) ? 'checked' : '' }}>
                                        <span>Akademik</span>
                                    </label>
                                    <label class="flex items-center space-x-2">
                                        <input type="checkbox" name="jenis_guru[]" value="muadalah"
                                            {{ is_array(old('jenis_guru')) && in_array('muadalah', old('jenis_guru')) ? 'checked' : '' }}>
                                        <span>Muadalah</span>
                                    </label>
                                </div>
                                @error('jenis_guru') <p class="text-red-500 text-sm mt-1">{{ $message }}</p> @enderror
                            </div>
                        </div>
                    </div>
                    <button type="submit" class="bg-[#8E412E] text-white px-4 py-2 rounded-md text-xs hover:bg-[#BA6F4D] shadow">
                        <i class="bi bi-save mr-1"></i> Simpan
                    </button>
                </form>
            </div>
        </div>
    </div>

    <script>
        const nameInput = document.querySelector('input[name="name"]');
        const namaLengkapInput = document.querySelector('input[name="nama_lengkap"]');
        const roleSelect = document.getElementById('roleSelect');
        const karyawanFields = document.getElementById('karyawanFields');
        const guruFields = document.getElementById('guruFields');
        const divisiField = document.getElementById('divisiField');

        function toggleKaryawanFields() {
            const isKaryawan = roleSelect.value === 'karyawan';
            const isGuru = roleSelect.value === 'guru';
            const showFields = isKaryawan || isGuru;

            karyawanFields.style.display = showFields ? 'block' : 'none';
            guruFields.style.display = isGuru ? 'block' : 'none';
            divisiField.style.display = isGuru ? 'none' : 'block'; // <-- hide divisi if guru

            const inputs = karyawanFields.querySelectorAll('input, select, textarea');
            inputs.forEach(input => {
                if (showFields && input.name !== 'divisi_id') {
                    input.required = ['nama_lengkap'].includes(input.name);
                }
                if (input.name === 'divisi_id') {
                    input.required = isKaryawan;
                }
            });

            // Auto-fill nama_lengkap from name
            if (showFields && !namaLengkapInput.value) {
                namaLengkapInput.value = nameInput.value;
            }
        }

        nameInput.addEventListener('input', () => {
            if (['karyawan', 'guru'].includes(roleSelect.value)) {
                if (!namaLengkapInput.value) {
                    namaLengkapInput.value = nameInput.value;
                }
            }
        });

        roleSelect.addEventListener('change', () => {
            toggleKaryawanFields();
            if (!['karyawan', 'guru'].includes(roleSelect.value)) {
                namaLengkapInput.value = '';
            }
        });

        window.addEventListener('DOMContentLoaded', () => {
            toggleKaryawanFields();
        });
    </script>
</x-app-layout>
