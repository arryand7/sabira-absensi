<x-app-shell>
    <h2 class="font-semibold text-xl text-[var(--sabira-ink)]">
        {{ __('Manajemen Kelas') }}
    </h2>
<div class="mt-6 w-full sm:px-6 lg:px-8 space-y-6">
        <div class="bg-[var(--sabira-surface)] shadow-md rounded-2xl p-6">
            <div class="mb-4 flex flex-wrap gap-3">
                <a href="{{ route('admin.class-groups.create') }}"
                class="inline-flex items-center gap-1 bg-[var(--sabira-primary)] hover:bg-[var(--sabira-primary-active)] text-white font-medium px-4 py-2 rounded shadow">
                    <i class="bi bi-plus-circle-fill"></i> Tambah Kelas
                </a>
                <button
                    onclick="document.getElementById('formDuplikat').classList.toggle('hidden')"
                    class="inline-flex items-center gap-1 bg-[var(--sabira-primary)] hover:bg-[var(--sabira-primary-active)] text-white font-medium px-4 py-2 rounded shadow"
                >
                    <i class="bi bi-files"></i> Duplikat Kelas
                </button>
            </div>
            <div id="formDuplikat" class="hidden bg-white p-4 rounded-xl shadow-md border border-[var(--sabira-border)] w-full mt-1 mb-2">
                <form method="POST" action="{{ route('admin.class-groups.duplicate') }}" class="flex flex-wrap items-end gap-4">
                    @csrf
                    <div>
                        <label class="block text-sm font-medium text-[var(--sabira-body)] mb-1">Tahun Ajaran Asal</label>
                        <select name="source_year" class="rounded-lg border-[var(--sabira-border)] shadow-sm focus:ring-[#C6D2B2] focus:border-[var(--sabira-border)]">
                            @foreach($academicYears as $year)
                                <option value="{{ $year->id }}">{{ $year->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-[var(--sabira-body)] mb-1">Tahun Ajaran Tujuan</label>
                        <select name="target_year" class="rounded-lg border-[var(--sabira-border)] shadow-sm focus:ring-[#C6D2B2] focus:border-[var(--sabira-border)]">
                            @foreach($academicYears as $year)
                                <option value="{{ $year->id }}">{{ $year->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <button type="submit" class="bg-[var(--sabira-primary)] hover:bg-[var(--sabira-primary-active)] text-white px-4 py-2 rounded shadow">
                            Duplikat Sekarang
                        </button>
                    </div>
                </form>
            </div>

            <div class="overflow-x-auto">
                <table id="kelasTable" class="w-full text-sm text-left text-[var(--sabira-body)]">
                    <thead class="bg-[var(--sabira-neutral-strong)] text-white uppercase text-xs font-semibold">
                        <tr>
                            <th class="px-4 py-3">Nama Kelas</th>
                            <th class="px-4 py-3">Jenis Kelas</th>
                            <th class="px-4 py-3">Tahun Ajaran</th>
                            <th class="px-4 py-3">Wali Kelas</th>
                            <th class="px-4 py-3 text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#D6D8D2]">
                        @if ($classGroups->count() > 0)
                            @foreach ($classGroups as $group)
                                <tr class="hover:bg-[var(--sabira-surface-strong)] transition">
                                    <td class="px-4 py-3">{{ $group->nama_kelas }}</td>
                                    <td class="px-4 py-3">
                                        @if ($group->jenis_kelas == 'formal')
                                            Reguler
                                        @elseif($group->jenis_kelas == 'muadalah')
                                            Non Reguler
                                        @elseif($group->jenis_kelas == 'tambahan')
                                            Tambahan
                                        @endif
                                    </td>
                                    <td class="px-4 py-3">{{ $group->academicYear->name }}</td>
                                    <td class="px-4 py-3">
                                        {{ $group->waliKelas?->user?->name ?? '-' }}
                                    </td>
                                    <td class="px-4 py-3 text-center space-x-1">
                                        <a href="{{ route('admin.class-groups.edit', $group->id) }}"
                                        class="inline-flex items-center gap-1 px-3 py-1 bg-yellow-500 text-white text-xs rounded hover:bg-yellow-600 shadow">
                                            <i class="bi bi-pencil-fill"></i> Edit
                                        </a>
                                        <form action="{{ route('admin.class-groups.destroy', $group->id) }}"
                                            method="POST" class="delete-form inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit"
                                                    class="inline-flex items-center gap-1 px-3 py-1 bg-red-600 text-white text-xs rounded hover:bg-red-700 shadow">
                                                <i class="bi bi-trash-fill"></i> Hapus
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        @else
                            <tr>
                                <td colspan="5" class="px-4 py-4 text-center text-gray-500">Belum ada kelas</td>
                            </tr>
                        @endif
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</x-app-shell>
