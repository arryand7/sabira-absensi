<x-app-shell headerTitle="Manajemen Karyawan & Guru" headerSubtitle="Data Pegawai, Guru, & Karyawan Sekolah">
    <div class="space-y-6">
        <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
            <div>
                <h3 class="text-base font-bold text-slate-900 dark:text-white">Daftar Karyawan & Guru</h3>
                <p class="text-xs text-slate-500">Kelola data profil pegawai, unit divisi, dan jabatan</p>
            </div>
            <a href="{{ route('karyawan.create') }}" class="inline-flex items-center gap-2 rounded-xl bg-[var(--sabira-primary)] px-4 py-2.5 text-xs font-bold text-white hover:bg-[var(--sabira-primary-active)] shadow-md shadow-indigo-600/30 transition">
                <i class="fas fa-user-plus"></i> <span>Tambah Karyawan</span>
            </a>
        </div>

        <div class="rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 p-6 shadow-sm">
            <div class="overflow-x-auto">
                <table class="min-w-full text-xs text-left">
                    <thead class="bg-slate-50 dark:bg-slate-800 text-slate-500 font-bold uppercase border-b border-slate-200 dark:border-slate-700">
                        <tr>
                            <th class="px-4 py-3">Nama Lengkap</th>
                            <th class="px-4 py-3">User Email</th>
                            <th class="px-4 py-3">Divisi</th>
                            <th class="px-4 py-3">Nomor HP</th>
                            <th class="px-4 py-3">Role</th>
                            <th class="px-4 py-3 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                        @forelse($karyawans as $karyawan)
                            <tr class="hover:bg-slate-50/60 dark:hover:bg-slate-800/40">
                                <td class="px-4 py-3 font-semibold text-slate-900 dark:text-white">{{ $karyawan->user->name ?? '-' }}</td>
                                <td class="px-4 py-3 text-slate-500">{{ $karyawan->user->email ?? '-' }}</td>
                                <td class="px-4 py-3"><span class="px-2 py-0.5 rounded-md bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 font-medium">{{ $karyawan->divisi?->nama ?? '-' }}</span></td>
                                <td class="px-4 py-3 text-slate-600 dark:text-slate-400">{{ $karyawan->no_hp ?? '-' }}</td>
                                <td class="px-4 py-3">
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full bg-indigo-50 text-indigo-700 dark:bg-indigo-950 dark:text-indigo-300 text-[10px] font-bold uppercase">
                                        {{ $karyawan->user->role ?? '-' }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-right space-x-2">
                                    <a href="{{ route('karyawan.show', $karyawan->id) }}" class="text-indigo-600 font-semibold hover:text-indigo-700">
                                        <i class="fas fa-eye"></i> Detail
                                    </a>
                                    <a href="{{ route('karyawan.edit', $karyawan) }}" class="text-amber-600 font-semibold hover:text-amber-700"><i class="fas fa-edit"></i> Edit</a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-4 py-6 text-center text-slate-400">Belum ada data Karyawan terdaftar.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-shell>
