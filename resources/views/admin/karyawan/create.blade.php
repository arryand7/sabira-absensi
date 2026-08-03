<x-app-shell headerTitle="Tambah Karyawan Baru" headerSubtitle="Form Registrasi Profil Pegawai">
    <div class="max-w-2xl mx-auto space-y-6">
        <div class="rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 p-6 shadow-sm">
            <div class="flex items-center justify-between pb-4 border-b border-slate-100 dark:border-slate-800 mb-6">
                <div>
                    <h3 class="text-base font-bold text-slate-900 dark:text-white">Form Registrasi Karyawan</h3>
                    <p class="text-xs text-slate-500">Hubungkan akun user dengan data detail pegawai</p>
                </div>
                <a href="{{ route('karyawan.index') }}" class="text-xs font-semibold text-slate-500 hover:text-slate-700">
                    <i class="fas fa-arrow-left mr-1"></i> Kembali
                </a>
            </div>

            <form action="{{ route('karyawan.store') }}" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-semibold text-slate-500 mb-1">Pilih Akun User</label>
                    <select name="user_id" class="w-full rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 p-2.5 text-xs" required>
                        <option value="">-- Pilih User --</option>
                        @foreach($users as $u)
                            <option value="{{ $u->id }}">{{ $u->name }} ({{ $u->email }}) - Role: {{ strtoupper($u->role) }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-slate-500 mb-1">Divisi / Unit</label>
                        <select name="divisi_id" class="w-full rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 p-2.5 text-xs">
                            <option value="">Tanpa divisi</option>
                            @foreach($divisis as $divisi)<option value="{{ $divisi->id }}" @selected(old('divisi_id') == $divisi->id)>{{ $divisi->nama }}</option>@endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-500 mb-1">Nomor HP</label>
                        <input type="text" name="no_hp" value="{{ old('no_hp') }}" class="w-full rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 p-2.5 text-xs" placeholder="08xxxxxxxxxx">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-500 mb-1">Alamat</label>
                    <textarea name="alamat" rows="3" class="w-full rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 p-2.5 text-xs">{{ old('alamat') }}</textarea>
                </div>

                <div class="flex justify-end gap-2 pt-4 border-t border-slate-100 dark:border-slate-800">
                    <a href="{{ route('karyawan.index') }}" class="rounded-lg border border-slate-300 px-4 py-2 text-xs font-semibold">Batal</a>
                    <button type="submit" class="rounded-lg bg-[var(--sabira-primary)] text-white px-5 py-2 text-xs font-bold hover:bg-[var(--sabira-primary-active)] shadow-md">Simpan Data Karyawan</button>
                </div>
            </form>
        </div>
    </div>
</x-app-shell>
