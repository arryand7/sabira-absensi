<x-app-shell>
<div class="flex">
        <div class="mt-6 w-full sm:px-6 lg:px-8 space-y-6">
            <div class="mb-4">
                <button onclick="window.history.back();"
                    class="bg-gray-300 hover:bg-gray-400 text-gray-800 px-4 py-2 rounded-md shadow flex items-center gap-2">
                    <i class="bi bi-arrow-left-circle-fill"></i> Kembali
                </button>
            </div>

            <div class="bg-[var(--sabira-neutral-strong)] shadow rounded-xl p-6 max-h-[calc(100vh-100px)] overflow-y-auto">
                <h1 class="text-2xl font-bold text-[var(--sabira-ink)] mb-4">Edit User</h1>

                <form action="{{ route('users.update', $user->id) }}" method="POST" enctype="multipart/form-data" class="space-y-4 w-full">
                    @csrf
                    @method('PUT')

                    <!-- Nama -->
                    <div>
                        <label class="block text-[var(--sabira-ink)] mb-1">Nama</label>
                        <input type="text" name="name"
                            class="w-full rounded border-gray-300 bg-[var(--sabira-surface)] text-[var(--sabira-ink)]"
                            value="{{ old('name', $user->name) }}" required>
                        @error('name') <p class="text-red-500 text-sm mt-1">{{ $message }}</p> @enderror
                    </div>

                    <!-- Email -->
                    <div>
                        <label class="block text-[var(--sabira-ink)]">Email</label>
                        <input type="email" name="email"
                            class="w-full rounded border-gray-300 bg-[var(--sabira-surface)] text-[var(--sabira-ink)]"
                            value="{{ old('email', $user->email) }}" required>
                        @error('email') <p class="text-red-500 text-sm mt-1">{{ $message }}</p> @enderror
                    </div>

                    <!-- Role -->
                    <div>
                        <label class="block text-[var(--sabira-ink)]">Role</label>
                        <select name="role" id="roleSelect"
                            class="w-full rounded border-gray-300 bg-[var(--sabira-surface)] text-[var(--sabira-ink)]"
                            required>
                            <option value="">-- Pilih Role --</option>
                            <option value="admin" {{ old('role', $user->role) == 'admin' ? 'selected' : '' }}>Admin</option>
                            @if(auth()->user()->isSuperAdmin())<option value="super_admin" {{ old('role', $user->role) == 'super_admin' ? 'selected' : '' }}>Superadmin</option>@endif
                            <option value="karyawan" {{ old('role', $user->role) == 'karyawan' ? 'selected' : '' }}>Karyawan</option>
                            <option value="guru" {{ old('role', $user->role) == 'guru' ? 'selected' : '' }}>Guru</option>
                            <option value="organisasi" {{ old('role', $user->role) == 'organisasi' ? 'selected' : '' }}>Organisasi</option>
                            <option value="siswa" {{ old('role', $user->role) == 'siswa' ? 'selected' : '' }}>Siswa</option>
                            <option value="wali" {{ old('role', $user->role) == 'wali' ? 'selected' : '' }}>Wali Siswa</option>
                        </select>
                        @error('role') <p class="text-red-500 text-sm mt-1">{{ $message }}</p> @enderror
                    </div>

                    <!-- Password -->
                    <div>
                        <label class="block text-[var(--sabira-ink)]">Password (Kosongkan jika tidak diubah)</label>
                        <input type="password" name="password"
                            class="w-full rounded border-gray-300 bg-[var(--sabira-surface)] text-[var(--sabira-ink)]">
                        @error('password') <p class="text-red-500 text-sm mt-1">{{ $message }}</p> @enderror
                    </div>

                    <!-- Status -->
                    <div>
                        <label class="block text-[var(--sabira-ink)] mb-1">Status</label>
                        <label class="inline-flex items-center cursor-pointer">
                            <input type="hidden" name="status" value="nonaktif">
                            <input type="checkbox" id="statusCheckbox" name="status" value="aktif"
                                {{ old('status', $user->status) === 'aktif' ? 'checked' : '' }}>
                            <span class="ml-3 text-sm text-gray-700">Aktif</span>
                        </label>
                    </div>

                    <!-- Data Karyawan -->
                    <div id="karyawanFields" style="display: none;" class="border-t pt-4 space-y-4">
                        <h3 class="text-lg font-semibold text-[var(--sabira-ink)]">Data Karyawan</h3>

                        <!-- Divisi -->
                        <div id="divisiField">
                            <label class="block text-[var(--sabira-ink)]">Divisi</label>
                            <select name="divisi_id"
                                class="w-full rounded border-gray-300 bg-[var(--sabira-surface)] text-[var(--sabira-ink)]">
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
                            <label class="block text-[var(--sabira-ink)]">Jenis Guru</label>
                            <select name="jenis"
                                class="w-full rounded border-gray-300 bg-[var(--sabira-surface)] text-[var(--sabira-ink)]">
                                <option value="">-- Pilih Jenis Guru --</option>
                                <option value="formal" {{ old('jenis', optional($user->guru)->jenis) == 'formal' ? 'selected' : '' }}>Formal</option>
                                <option value="muadalah" {{ old('jenis', optional($user->guru)->jenis) == 'muadalah' ? 'selected' : '' }}>Muadalah</option>
                            </select>
                            @error('jenis') <p class="text-red-500 text-sm mt-1">{{ $message }}</p> @enderror
                        </div>

                        <!-- Alamat -->
                        <div>
                            <label class="block text-[var(--sabira-ink)]">Alamat</label>
                            <textarea name="alamat"
                                class="w-full rounded border-gray-300 bg-[var(--sabira-surface)] text-[var(--sabira-ink)]">{{ old('alamat', $user->karyawan->alamat ?? '') }}</textarea>
                            @error('alamat') <p class="text-red-500 text-sm mt-1">{{ $message }}</p> @enderror
                        </div>

                        <!-- No HP -->
                        <div>
                            <label class="block text-[var(--sabira-ink)]">No HP</label>
                            <input type="text" name="no_hp"
                                value="{{ old('no_hp', $user->karyawan->no_hp ?? '') }}"
                                class="w-full rounded border-gray-300 bg-[var(--sabira-surface)] text-[var(--sabira-ink)]">
                            @error('no_hp') <p class="text-red-500 text-sm mt-1">{{ $message }}</p> @enderror
                        </div>

                        <!-- Foto -->
                        <div>
                            <label class="block text-[var(--sabira-ink)]">Foto (Kosongkan jika tidak diubah)</label>
                            <input type="file" name="foto"
                                class="w-full rounded border-gray-300 bg-[var(--sabira-surface)] text-[var(--sabira-ink)]">
                            @error('foto') <p class="text-red-500 text-sm mt-1">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    <!-- Submit -->
                    <button type="submit" class="bg-[var(--sabira-primary)] text-white px-4 py-2 rounded-md text-xs hover:bg-[var(--sabira-primary-active)] shadow">
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
</x-app-shell>
