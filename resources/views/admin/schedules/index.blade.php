<x-app-shell>
    <div class="sm:px-6 lg:px-8">
        <x-page-title title="DAFTAR JADWAL" />
    </div>
<div class="mt-6 w-full sm:px-6 lg:px-8 space-y-6">
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <div class="bg-[var(--sabira-surface-soft)] border border-[var(--sabira-border)] rounded-xl p-4">
                <p class="text-xs uppercase tracking-wide text-[var(--sabira-muted)]">Total Jadwal</p>
                <p class="mt-2 text-2xl font-semibold text-[var(--sabira-ink)]">{{ $summary['total'] }}</p>
            </div>
            <div class="bg-[#ECFDF5] border border-[#A7F3D0] rounded-xl p-4">
                <p class="text-xs uppercase tracking-wide text-[#065F46]">Guru Terjadwal</p>
                <p class="mt-2 text-2xl font-semibold text-[#065F46]">{{ $summary['teachers'] }}</p>
            </div>
            <div class="bg-[#E0E7FF] border border-[#A5B4FC] rounded-xl p-4">
                <p class="text-xs uppercase tracking-wide text-[#3730A3]">Kelas Terjadwal</p>
                <p class="mt-2 text-2xl font-semibold text-[#3730A3]">{{ $summary['classes'] }}</p>
            </div>
        </div>

        <div class="sabira-card space-y-5">
            <form action="{{ route('admin.schedules.index') }}" method="GET" class="grid gap-4 sm:grid-cols-2 lg:grid-cols-5">
                <div>
                    <label class="mb-1.5 block text-sm font-medium text-[var(--sabira-body)]">Tahun Ajaran</label>
                    <select name="tahun_ajaran" class="sabira-select">
                        @foreach($academicYears as $year)
                            <option value="{{ $year->id }}" {{ (string) $selectedYear === (string) $year->id ? 'selected' : '' }}>
                                {{ $year->name }}{{ $year->is_active ? ' (Aktif)' : '' }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="mb-1.5 block text-sm font-medium text-[var(--sabira-body)]">Guru</label>
                    <select name="guru_id" class="sabira-select">
                        <option value="">Semua</option>
                        @foreach($teachers as $teacher)
                            <option value="{{ $teacher->id }}" {{ request('guru_id') == $teacher->id ? 'selected' : '' }}>
                                {{ $teacher->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="mb-1.5 block text-sm font-medium text-[var(--sabira-body)]">Kelas</label>
                    <select name="class_group_id" class="sabira-select">
                        <option value="">Semua</option>
                        @foreach($classGroups as $group)
                            <option value="{{ $group->id }}" {{ request('class_group_id') == $group->id ? 'selected' : '' }}>
                                {{ $group->nama_kelas }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="mb-1.5 block text-sm font-medium text-[var(--sabira-body)]">Mata Pelajaran</label>
                    <select name="subject_id" class="sabira-select">
                        <option value="">Semua</option>
                        @foreach($subjects as $subject)
                            <option value="{{ $subject->id }}" {{ request('subject_id') == $subject->id ? 'selected' : '' }}>
                                {{ $subject->nama_mapel }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="mb-1.5 block text-sm font-medium text-[var(--sabira-body)]">Semester</label>
                    <select name="semester" class="sabira-select">
                        <option value="ganjil" @selected($selectedSemester === 'ganjil')>Ganjil</option>
                        <option value="genap" @selected($selectedSemester === 'genap')>Genap</option>
                    </select>
                </div>
                <div class="sm:col-span-2 lg:col-span-5 flex flex-col-reverse gap-2 border-t border-[var(--sabira-border-soft)] pt-4 sm:flex-row sm:flex-wrap sm:items-center sm:justify-between">
                    <div class="grid grid-cols-2 gap-2 sm:flex sm:flex-wrap">
                        <button type="submit" class="sabira-button sabira-button-primary w-full sm:w-auto">
                            <i class="bi bi-funnel-fill"></i> Tampilkan
                        </button>
                        <a href="{{ route('admin.schedules.index') }}"
                            class="sabira-button sabira-button-secondary w-full sm:w-auto">
                            <i class="bi bi-x-circle-fill"></i> Reset
                        </a>
                    </div>
                    <div class="flex flex-col gap-2 sm:flex-row sm:flex-wrap">
                        <a href="{{ route('admin.schedules.create', ['guru_id' => request('guru_id'), 'tahun_ajaran' => request('tahun_ajaran')]) }}"
                            class="sabira-button sabira-button-primary w-full sm:w-auto">
                            <i class="bi bi-plus-circle-fill"></i> Tambah Jadwal
                        </a>
                        @if(request('guru_id'))
                            <a href="{{ route('admin.schedules.show-by-teacher', request('guru_id')) }}"
                                class="sabira-button sabira-button-secondary w-full sm:w-auto">
                                <i class="bi bi-person-badge-fill"></i> Kelola Jadwal Guru
                            </a>
                        @endif
                    </div>
                </div>
            </form>
        </div>

        <div class="sabira-card overflow-hidden p-0 schedule-table">
            <div class="overflow-x-auto">
                <table id="jadwalTable" class="sabira-data-table min-w-[1060px] text-xs">
                    <thead>
                        <tr>
                            <th class="px-3 py-2">Hari</th>
                            <th class="px-3 py-2">Mulai</th>
                            <th class="px-3 py-2">Selesai</th>
                            <th class="px-3 py-2">Kelas</th>
                            <th class="px-3 py-2">Kode</th>
                            <th class="px-3 py-2">Mata Pelajaran</th>
                            <th class="px-3 py-2">Guru</th>
                            <th class="w-[180px] text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($schedules as $schedule)
                            <tr class="hover:bg-[var(--sabira-surface-soft)] transition">
                                <td class="px-3 py-2">{{ $schedule->hari }}</td>
                                <td class="px-3 py-2">{{ $schedule->jam_mulai }}</td>
                                <td class="px-3 py-2">{{ $schedule->jam_selesai }}</td>
                                <td class="px-3 py-2">{{ $schedule->classGroup->nama_kelas }}</td>
                                <td class="px-3 py-2">{{ $schedule->subject->kode_mapel ?? '-' }}</td>
                                <td class="px-3 py-2">{{ $schedule->subject->nama_mapel }} @if($schedule->has_pending_conflict)<a href="{{ route('admin.schedule-conflicts.index', ['status' => 'pending_review', 'teacher_id' => $schedule->user_id]) }}" class="ml-1 inline-flex items-center gap-1 text-[10px] font-semibold text-[var(--sabira-warning)]"><i class="fas fa-triangle-exclamation"></i> Bentrok</a>@endif</td>
                                <td class="px-3 py-2">{{ $schedule->teacher->name }}</td>
                                <td class="w-[180px] whitespace-nowrap text-center">
                                    <div class="inline-flex items-center justify-center gap-2" role="group" aria-label="Aksi jadwal {{ $schedule->subject->nama_mapel }}">
                                        <a href="{{ route('admin.schedules.edit', $schedule->id) }}"
                                            class="sabira-icon-button h-11 w-11" title="Edit jadwal" aria-label="Edit jadwal">
                                            <i class="bi bi-pencil-fill" aria-hidden="true"></i>
                                        </a>
                                        <button type="button" onclick="document.getElementById('substitute-{{ $schedule->id }}').showModal()"
                                            class="sabira-icon-button h-11 w-11" title="Tetapkan guru pengganti" aria-label="Tetapkan guru pengganti">
                                            <i class="bi bi-person-check-fill" aria-hidden="true"></i>
                                        </button>
                                        <form action="{{ route('admin.schedules.destroy', $schedule->id) }}"
                                            method="POST" class="inline-flex delete-form">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit"
                                                class="sabira-icon-button h-11 w-11 text-[var(--sabira-danger)]" title="Hapus jadwal" aria-label="Hapus jadwal">
                                                <i class="bi bi-trash-fill" aria-hidden="true"></i>
                                            </button>
                                        </form>
                                    </div>
                                    <dialog id="substitute-{{ $schedule->id }}" class="rounded-xl p-0 shadow-2xl backdrop:bg-slate-900/60 text-left w-full max-w-md">
                                        <form action="{{ route('admin.schedules.assign-substitute', $schedule) }}" method="POST" class="p-5 space-y-4">
                                            @csrf
                                            <div class="flex items-start justify-between gap-4">
                                                <div>
                                                    <h3 class="text-sm font-bold text-slate-900">Tetapkan Guru Pengganti</h3>
                                                    <p class="mt-1 text-xs text-slate-500">{{ $schedule->subject->nama_mapel }} · {{ $schedule->classGroup->nama_kelas }}</p>
                                                </div>
                                                <button type="button" onclick="this.closest('dialog').close()" class="text-slate-400 hover:text-slate-700" aria-label="Tutup">&times;</button>
                                            </div>
                                            <div>
                                                <label class="block text-xs font-semibold text-slate-700">Tanggal sesi</label>
                                                <input type="date" name="date" value="{{ now()->toDateString() }}" required class="mt-1 w-full rounded-md border-slate-300 text-sm">
                                            </div>
                                            <div>
                                                <label class="block text-xs font-semibold text-slate-700">Guru pengganti</label>
                                                <select name="substitute_teacher_id" required class="mt-1 w-full rounded-md border-slate-300 text-sm">
                                                    <option value="">Pilih guru</option>
                                                    @foreach($teachers->where('id', '!=', $schedule->user_id) as $teacherOption)
                                                        <option value="{{ $teacherOption->id }}">{{ $teacherOption->name }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div class="flex justify-end gap-2">
                                                <button type="button" onclick="this.closest('dialog').close()" class="rounded-md border border-slate-300 px-3 py-2 text-xs font-semibold">Batal</button>
                                                <button type="submit" class="rounded-md bg-[var(--sabira-primary)] px-3 py-2 text-xs font-bold text-white hover:bg-[var(--sabira-primary-active)]">Simpan Penugasan</button>
                                            </div>
                                        </form>
                                    </dialog>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="px-4 py-6 text-center text-[var(--sabira-muted)]">
                                    Belum ada jadwal untuk filter yang dipilih.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="sabira-card">
            <h3 class="text-sm font-semibold text-[var(--sabira-ink)] mb-3">Import Jadwal</h3>

            @if (session('success') || session('errors_import'))
                <div class="mb-4 space-y-2">
                    @if (session('success') && is_array(session('success')))
                        <div class="mb-4 px-4 py-2 bg-green-100 text-green-700 rounded shadow space-y-1">
                            @foreach (session('success') as $msg)
                                <div>{{ $msg }}</div>
                            @endforeach
                        </div>
                    @endif

                    @if (session('errors_import'))
                        <div class="bg-red-100 text-red-800 px-4 py-3 rounded shadow">
                            <strong>Gagal:</strong>
                            <ul class="list-disc ml-5 text-sm">
                                @foreach (session('errors_import') as $msg)
                                    <li>{{ $msg }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                </div>
            @endif

            @error('file')
                <div class="bg-red-100 text-red-800 px-4 py-2 rounded shadow mt-2 text-sm">
                    {{ $message }}
                </div>
            @enderror

            <form action="{{ route('admin.schedules.import') }}" method="POST" enctype="multipart/form-data" class="flex flex-wrap items-center gap-3">
                @csrf
                <input type="file" name="file" required class="form-input" />
                <button type="submit" class="bg-[var(--sabira-primary)] hover:bg-[var(--sabira-primary-active)] text-white px-4 py-2 rounded">
                    <i class="bi bi-upload"></i> Import
                </button>
            </form>
        </div>
    </div>

    @push('styles')
        <style>
            .schedule-table .dataTables_wrapper {
                font-size: 12px;
            }
            .schedule-table .dataTables_length label,
            .schedule-table .dataTables_filter label {
                margin: 0;
                font-weight: 600;
                color: #1C1E17;
            }
            .schedule-table .dataTables_filter input,
            .schedule-table .dataTables_length select {
                height: 28px;
                padding: 0 8px;
                font-size: 12px;
            }
            .schedule-table .dataTables_paginate .pagination {
                margin: 0;
            }
            .schedule-table table.dataTable thead th,
            .schedule-table table.dataTable tbody td {
                padding: 8px 10px;
            }
            .schedule-table table.dataTable {
                border-collapse: collapse !important;
            }
        </style>
    @endpush

</x-app-shell>
