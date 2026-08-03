<x-app-shell headerTitle="Manajemen Program Pendidikan" headerSubtitle="Pengelolaan Program Formal & Muadalah">
    <div class="space-y-6">
        <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
            <div>
                <h3 class="text-base font-bold text-slate-900 dark:text-white">Daftar Program Pendidikan</h3>
                <p class="text-xs text-slate-500">Kelola jenjang dan kategori program pendidikan sekolah</p>
            </div>
            <button onclick="document.getElementById('modal-create-program').classList.remove('hidden')" class="inline-flex items-center gap-2 rounded-xl bg-[var(--sabira-primary)] px-4 py-2.5 text-xs font-bold text-white hover:bg-[var(--sabira-primary-active)] shadow-md shadow-indigo-600/30 transition">
                <i class="fas fa-plus"></i> <span>Tambah Program</span>
            </button>
        </div>

        <div class="rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 p-6 shadow-sm">
            <div class="overflow-x-auto">
                <table class="min-w-full text-xs text-left">
                    <thead class="bg-slate-50 dark:bg-slate-800 text-slate-500 font-bold uppercase border-b border-slate-200 dark:border-slate-700">
                        <tr>
                            <th class="px-4 py-3">Kode</th>
                            <th class="px-4 py-3">Nama Program</th>
                            <th class="px-4 py-3">Jam Definisikan</th>
                            <th class="px-4 py-3">Total Kelas</th>
                            <th class="px-4 py-3">Total Guru</th>
                            <th class="px-4 py-3">Status</th>
                            <th class="px-4 py-3 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                        @forelse($programs as $program)
                            <tr class="hover:bg-slate-50/60 dark:hover:bg-slate-800/40">
                                <td class="px-4 py-3 font-mono font-bold text-indigo-600 dark:text-indigo-400">{{ $program->code }}</td>
                                <td class="px-4 py-3 font-semibold text-slate-900 dark:text-white">{{ $program->name }}</td>
                                <td class="px-4 py-3 text-slate-500 font-mono">
                                    {{ $program->default_start_time ? substr($program->default_start_time, 0, 5) : '-' }} - {{ $program->default_end_time ? substr($program->default_end_time, 0, 5) : '-' }}
                                </td>
                                <td class="px-4 py-3 font-semibold">{{ $program->class_groups_count }} Kelas</td>
                                <td class="px-4 py-3 font-semibold">{{ $program->teachers_count }} Guru</td>
                                <td class="px-4 py-3">
                                    <x-status-badge :status="$program->is_active ? 'aktif' : 'nonaktif'" size="sm" />
                                </td>
                                <td class="px-4 py-3 text-right space-x-2">
                                    <a href="{{ route('admin.schedule-time-slots.index', ['program_id' => $program->id]) }}" class="font-semibold text-[var(--sabira-primary)] hover:text-[var(--sabira-primary-active)]">
                                        <i class="far fa-clock"></i> Atur jam
                                    </a>
                                    <button onclick="document.getElementById('modal-edit-{{ $program->id }}').classList.remove('hidden')" class="text-indigo-600 font-semibold hover:text-indigo-700">
                                        <i class="fas fa-edit"></i> Edit
                                    </button>
                                    <form action="{{ route('admin.education-programs.destroy', $program->id) }}" method="POST" class="inline delete-form">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-rose-600 font-semibold hover:text-rose-700">
                                            <i class="fas fa-trash"></i> Hapus
                                        </button>
                                    </form>
                                </td>
                            </tr>

                            <!-- Modal Edit Program -->
                            <div id="modal-edit-{{ $program->id }}" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-slate-900/60 backdrop-blur-sm p-4">
                                <div class="w-full max-w-md rounded-2xl bg-white dark:bg-slate-900 p-6 shadow-2xl border border-slate-200 dark:border-slate-800">
                                    <div class="flex items-center justify-between pb-3 border-b border-slate-100 dark:border-slate-800 mb-4">
                                        <h4 class="text-sm font-bold text-slate-900 dark:text-white">Edit Program Pendidikan</h4>
                                        <button onclick="document.getElementById('modal-edit-{{ $program->id }}').classList.add('hidden')" class="text-slate-400 hover:text-slate-600">
                                            <i class="fas fa-times"></i>
                                        </button>
                                    </div>
                                    <form action="{{ route('admin.education-programs.update', $program->id) }}" method="POST" class="space-y-4">
                                        @csrf
                                        @method('PUT')
                                        <div>
                                            <label class="block text-xs font-semibold text-slate-500 mb-1">Kode Program</label>
                                            <input type="text" name="code" value="{{ old('code', $program->code) }}" class="w-full rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 p-2.5 text-xs font-bold uppercase" required>
                                        </div>
                                        <div>
                                            <label class="block text-xs font-semibold text-slate-500 mb-1">Nama Program</label>
                                            <input type="text" name="name" value="{{ old('name', $program->name) }}" class="w-full rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 p-2.5 text-xs" required>
                                        </div>
                                        <div class="grid grid-cols-2 gap-4">
                                            <div>
                                                <label class="block text-xs font-semibold text-slate-500 mb-1">Jam Mulai Standard</label>
                                                <input type="time" name="default_start_time" value="{{ old('default_start_time', $program->default_start_time ? substr($program->default_start_time, 0, 5) : '') }}" class="w-full rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 p-2.5 text-xs">
                                            </div>
                                            <div>
                                                <label class="block text-xs font-semibold text-slate-500 mb-1">Jam Selesai Standard</label>
                                                <input type="time" name="default_end_time" value="{{ old('default_end_time', $program->default_end_time ? substr($program->default_end_time, 0, 5) : '') }}" class="w-full rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 p-2.5 text-xs">
                                            </div>
                                        </div>
                                        <div class="flex items-center gap-2 pt-2">
                                            <input type="checkbox" name="is_active" id="edit_active_{{ $program->id }}" value="1" {{ $program->is_active ? 'checked' : '' }} class="rounded border-slate-300 text-indigo-600">
                                            <label for="edit_active_{{ $program->id }}" class="text-xs font-semibold text-slate-700 dark:text-slate-300">Status Aktif</label>
                                        </div>
                                        <div class="flex justify-end gap-2 pt-2">
                                            <button type="button" onclick="document.getElementById('modal-edit-{{ $program->id }}').classList.add('hidden')" class="rounded-lg border border-slate-300 px-4 py-2 text-xs font-semibold">Batal</button>
                                            <button type="submit" class="rounded-lg bg-[var(--sabira-primary)] text-white px-4 py-2 text-xs font-bold hover:bg-[var(--sabira-primary-active)]">Simpan Perubahan</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        @empty
                            <tr>
                                <td colspan="7" class="px-4 py-6 text-center text-slate-400">Belum ada Program Pendidikan terdaftar.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Modal Create Program -->
    <div id="modal-create-program" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-slate-900/60 backdrop-blur-sm p-4">
        <div class="w-full max-w-md rounded-2xl bg-white dark:bg-slate-900 p-6 shadow-2xl border border-slate-200 dark:border-slate-800">
            <div class="flex items-center justify-between pb-3 border-b border-slate-100 dark:border-slate-800 mb-4">
                <h4 class="text-sm font-bold text-slate-900 dark:text-white">Tambah Program Pendidikan Baru</h4>
                <button onclick="document.getElementById('modal-create-program').classList.add('hidden')" class="text-slate-400 hover:text-slate-600">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <form action="{{ route('admin.education-programs.store') }}" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-semibold text-slate-500 mb-1">Kode Program (misal: FORMAL, MUADALAH)</label>
                    <input type="text" name="code" placeholder="FORMAL" class="w-full rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 p-2.5 text-xs font-bold uppercase" required>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-500 mb-1">Nama Program</label>
                    <input type="text" name="name" placeholder="Program Pendidikan Formal SMA/MA" class="w-full rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 p-2.5 text-xs" required>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-slate-500 mb-1">Jam Mulai Standard</label>
                        <input type="time" name="default_start_time" value="07:30" class="w-full rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 p-2.5 text-xs">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-500 mb-1">Jam Selesai Standard</label>
                        <input type="time" name="default_end_time" value="14:00" class="w-full rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 p-2.5 text-xs">
                    </div>
                </div>
                <div class="flex items-center gap-2 pt-2">
                    <input type="checkbox" name="is_active" id="create_active" value="1" checked class="rounded border-slate-300 text-indigo-600">
                    <label for="create_active" class="text-xs font-semibold text-slate-700 dark:text-slate-300">Status Aktif</label>
                </div>
                <div class="flex justify-end gap-2 pt-2">
                    <button type="button" onclick="document.getElementById('modal-create-program').classList.add('hidden')" class="rounded-lg border border-slate-300 px-4 py-2 text-xs font-semibold">Batal</button>
                    <button type="submit" class="rounded-lg bg-[var(--sabira-primary)] text-white px-4 py-2 text-xs font-bold hover:bg-[var(--sabira-primary-active)]">Simpan Program</button>
                </div>
            </form>
        </div>
    </div>
</x-app-shell>
