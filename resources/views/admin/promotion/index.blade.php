<x-app-shell headerTitle="Keanggotaan Siswa" headerSubtitle="Migrasi dan Pindah Siswa Per Kelas">
    <div x-data="promotionManager({
        allPageStudentIds: {{ json_encode($students->pluck('id')->values()) }},
        toClassId: '{{ $filters['to_class_id'] }}',
        targetClassType: '{{ $selectedTargetClass?->class_type ?? '' }}',
        targetClassName: '{{ $selectedTargetClass?->nama_kelas ?? '' }}',
        actionMode: '{{ $filters['action_mode'] }}',
        previewUrl: '{{ route('promotion.preview') }}',
        csrfToken: '{{ csrf_token() }}',
        studentsData: {{ json_encode($students->items()) }}
    })" class="space-y-6">

        {{-- Alert Info / Error Validation Summary jika ada --}}
        @if($errors->any())
            <div class="bg-rose-50 dark:bg-rose-950/40 border border-rose-200 dark:border-rose-800 rounded-xl p-4 text-sm text-rose-800 dark:text-rose-200">
                <div class="font-semibold flex items-center gap-2 mb-1">
                    <i class="fas fa-exclamation-triangle"></i> Terdapat kesalahan pengisian data:
                </div>
                <ul class="list-disc list-inside space-y-1 text-xs">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        {{-- Section 1: Top Bar & Target Class Selector Card --}}
        <div class="bg-[var(--sabira-surface)] border border-[var(--sabira-neutral-subtle,#e2e8f0)] dark:border-slate-800 rounded-2xl p-6 shadow-sm">
            <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-6">
                <div>
                    <h2 class="text-xl font-bold text-[var(--sabira-ink)] dark:text-white flex items-center gap-2.5">
                        <i class="fas fa-user-graduate text-[var(--sabira-primary)]"></i>
                        <span>Keanggotaan & Migrasi Siswa</span>
                    </h2>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">
                        Pilih kelas, saring daftar siswa, lalu Tambah, Pindah, atau Batalkan keanggotaan yang salah input.
                    </p>
                </div>

                <div class="flex items-center gap-3">
                    <button type="button" @click="clearSelections()"
                            x-show="selectedIds.length > 0"
                            class="inline-flex items-center gap-1.5 text-xs text-slate-600 dark:text-slate-300 bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 px-3 py-2 rounded-lg transition">
                        <i class="fas fa-trash-alt"></i>
                        <span>Reset Pilihan (<span x-text="selectedIds.length"></span>)</span>
                    </button>

                    <button type="button" @click="openPreviewModal()"
                            :disabled="selectedIds.length === 0 || !toClassId"
                            :class="(selectedIds.length > 0 && toClassId) ? 'bg-[var(--sabira-primary)] hover:bg-[var(--sabira-primary-active,#1e40af)] text-white shadow-md' : 'bg-slate-200 text-slate-400 dark:bg-slate-800 dark:text-slate-600 cursor-not-allowed'"
                            class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl font-semibold text-sm transition">
                        <i class="fas fa-arrow-right-circle"></i>
                        <span>Proses Keanggotaan</span>
                        <span x-show="selectedIds.length > 0" class="bg-white/20 px-2 py-0.5 rounded-full text-xs" x-text="selectedIds.length"></span>
                    </button>
                </div>
            </div>

            <hr class="my-5 border-slate-200 dark:border-slate-800">

            {{-- Target Class & Action Mode Controls --}}
            <div class="grid grid-cols-1 md:grid-cols-12 gap-5 items-end">
                <div class="md:col-span-6 lg:col-span-5">
                    <label for="to_class_id_select" class="block text-xs font-semibold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1.5">
                        <span x-text="actionMode === 'invalidate' ? '1. Kelas yang Akan Dibatalkan' : '1. Pilih Kelas Tujuan'"></span> <span class="text-rose-500">*</span>
                    </label>
                    <div class="relative">
                        <select id="to_class_id_select" x-model="toClassId" @change="onTargetClassChange($event)"
                                class="w-full rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-900 text-slate-800 dark:text-slate-100 px-3.5 py-2.5 text-sm focus:ring-2 focus:ring-[var(--sabira-primary)] focus:border-transparent">
                            <option value="" x-text="actionMode === 'invalidate' ? '-- Pilih Kelas yang Akan Dibatalkan --' : '-- Pilih Kelas Tujuan --'"></option>
                            @foreach($toClasses as $class)
                                <option value="{{ $class->id }}"
                                        data-[class-type]="{{ $class->class_type }}"
                                        data-[class-name]="{{ $class->nama_kelas }}"
                                        data-[program]="{{ $class->educationProgram?->name ?? 'Formal' }}"
                                        data-[academic]="{{ $class->academicYear?->name ?? '' }}"
                                        {{ (string)$filters['to_class_id'] === (string)$class->id ? 'selected' : '' }}>
                                    {{ $class->nama_kelas }} — {{ $class->educationProgram?->name ?? 'Formal' }} ({{ ucfirst($class->class_type) }}) [TA: {{ $class->academicYear?->name }}]
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="md:col-span-6 lg:col-span-7">
                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1.5">
                        2. Pilih Mode Tindakan <span class="text-rose-500">*</span>
                    </label>
                    <div class="inline-flex rounded-xl p-1 bg-slate-100 dark:bg-slate-900 border border-slate-200 dark:border-slate-800 w-full sm:w-auto">
                        <label class="flex-1 sm:flex-initial cursor-pointer">
                            <input type="radio" name="action_mode_radio" value="add" x-model="actionMode" @change="onActionModeChange" class="sr-only">
                            <span :class="actionMode === 'add' ? 'bg-white dark:bg-slate-800 text-[var(--sabira-primary)] shadow-sm font-semibold' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900'"
                                  class="flex items-center justify-center gap-1.5 px-4 py-2 rounded-lg text-xs transition">
                                <i class="fas fa-plus-circle text-xs"></i>
                                <span>Tambahkan ke Kelas</span>
                            </span>
                        </label>
                        <label class="flex-1 sm:flex-initial cursor-pointer" :class="targetClassType === 'non_reguler' ? 'opacity-50 cursor-not-allowed' : ''">
                            <input type="radio" name="action_mode_radio" value="transfer" x-model="actionMode" @change="onActionModeChange" :disabled="targetClassType === 'non_reguler'" class="sr-only">
                            <span :class="actionMode === 'transfer' ? 'bg-white dark:bg-slate-800 text-[var(--sabira-primary)] shadow-sm font-semibold' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900'"
                                  class="flex items-center justify-center gap-1.5 px-4 py-2 rounded-lg text-xs transition">
                                <i class="fas fa-exchange-alt text-xs"></i>
                                <span>Pindahkan Kelas Reguler</span>
                            </span>
                        </label>
                        <label class="flex-1 sm:flex-initial cursor-pointer">
                            <input type="radio" name="action_mode_radio" value="invalidate" x-model="actionMode" @change="onActionModeChange" class="sr-only">
                            <span :class="actionMode === 'invalidate' ? 'bg-white dark:bg-slate-800 text-rose-700 dark:text-rose-300 shadow-sm font-semibold' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900'"
                                  class="flex items-center justify-center gap-1.5 px-4 py-2 rounded-lg text-xs transition">
                                <i class="fas fa-ban text-xs"></i>
                                <span>Batalkan Keanggotaan</span>
                            </span>
                        </label>
                    </div>
                    <p class="text-[11px] text-slate-500 dark:text-slate-400 mt-1" x-text="actionModeDescription"></p>
                </div>
                <div x-show="actionMode === 'invalidate'" class="md:col-span-12">
                    <label for="invalidation_reason" class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Alasan Pembatalan <span class="text-rose-500">*</span></label>
                    <textarea id="invalidation_reason" x-model="invalidationReason" minlength="5" maxlength="1000" rows="3"
                              placeholder="Jelaskan kesalahan input keanggotaan (minimal 5 karakter)."
                              class="w-full rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-900 text-sm"></textarea>
                </div>
            </div>

            {{-- Target Class Meta Banner --}}
            @if($selectedTargetClass)
                <div class="mt-4 p-3.5 bg-blue-50/80 dark:bg-blue-950/30 border border-blue-200 dark:border-blue-800/50 rounded-xl flex flex-wrap items-center justify-between gap-3 text-xs text-blue-900 dark:text-blue-200">
                    <div class="flex items-center gap-2">
                        <i class="fas fa-info-circle text-blue-600 dark:text-blue-400"></i>
                        <span>Kelas Target Terpilih: <strong>{{ $selectedTargetClass->nama_kelas }}</strong></span>
                        <span class="px-2 py-0.5 rounded-full bg-blue-200/70 dark:bg-blue-900/60 font-medium">Program: {{ $selectedTargetClass->educationProgram?->name ?? 'Formal' }}</span>
                        <span class="px-2 py-0.5 rounded-full bg-blue-200/70 dark:bg-blue-900/60 font-medium">Jenis: {{ ucfirst($selectedTargetClass->class_type) }}</span>
                        <span class="px-2 py-0.5 rounded-full bg-blue-200/70 dark:bg-blue-900/60 font-medium">TA: {{ $selectedTargetClass->academicYear?->name }}</span>
                    </div>
                    <div class="text-[11px] text-blue-700 dark:text-blue-300">
                        {{ $selectedTargetClass->activeStudents->count() }} Anggota Aktif Saat Ini
                    </div>
                </div>
            @endif
        </div>

        {{-- Section 2: Server-Side Filter Bar --}}
        <div class="bg-[var(--sabira-surface)] border border-[var(--sabira-neutral-subtle,#e2e8f0)] dark:border-slate-800 rounded-2xl p-5 shadow-sm">
            <form id="filter-form" method="GET" action="{{ route('promotion.index') }}">
                <input type="hidden" name="to_class_id" :value="toClassId">
                <input type="hidden" name="action_mode" :value="actionMode">

                {{-- Header Filter & Toggle Mobile --}}
                <div class="flex items-center justify-between gap-4 mb-4">
                    <div class="flex items-center gap-2 text-sm font-bold text-slate-800 dark:text-slate-200">
                        <i class="fas fa-filter text-[var(--sabira-primary)]"></i>
                        <span>Filter Data Siswa</span>
                        @php
                            $activeCount = 0;
                            if($filters['search']) $activeCount++;
                            if($filters['program_id']) $activeCount++;
                            if($filters['class_type']) $activeCount++;
                            if($filters['source_class_group_id']) $activeCount++;
                            if($filters['grade_level']) $activeCount++;
                            if($filters['membership_status'] && $filters['membership_status'] !== 'all') $activeCount++;
                            if($filters['hide_target_members']) $activeCount++;
                        @endphp
                        @if($activeCount > 0)
                            <span class="bg-[var(--sabira-primary)] text-white text-[11px] font-bold px-2 py-0.5 rounded-full">
                                {{ $activeCount }} Aktif
                            </span>
                        @endif
                    </div>

                    <div class="flex items-center gap-2">
                        <button type="button" @click="mobileFiltersOpen = !mobileFiltersOpen"
                                class="sm:hidden text-xs px-3 py-1.5 rounded-lg bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 flex items-center gap-1.5">
                            <i class="fas fa-sliders-h"></i>
                            <span x-text="mobileFiltersOpen ? 'Tutup Filter' : 'Buka Filter'"></span>
                        </button>

                        <a href="{{ route('promotion.index', array_filter(['to_class_id' => $filters['to_class_id']])) }}"
                           class="text-xs text-slate-500 hover:text-slate-800 dark:text-slate-400 dark:hover:text-slate-200 flex items-center gap-1 px-2.5 py-1.5 rounded-lg border border-slate-200 dark:border-slate-700 transition">
                            <i class="fas fa-undo"></i>
                            <span>Reset Filter</span>
                        </a>
                    </div>
                </div>

                {{-- Filter Controls Grid --}}
                <div :class="mobileFiltersOpen ? 'block' : 'hidden sm:block'" class="space-y-4">
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-12 gap-3.5">
                        {{-- Search Input (Main Field) --}}
                        <div class="sm:col-span-2 lg:col-span-4">
                            <label for="filter_search" class="block text-[11px] font-medium text-slate-600 dark:text-slate-400 mb-1">
                                Cari Nama / NIS
                            </label>
                            <div class="relative">
                                <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                                    <i class="fas fa-search text-xs"></i>
                                </span>
                                <input type="text" id="filter_search" name="search" value="{{ $filters['search'] }}"
                                       placeholder="Ketik nama atau NIS..."
                                       class="w-full pl-9 pr-3 py-2 text-xs rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-900 text-slate-800 dark:text-slate-100 focus:ring-2 focus:ring-[var(--sabira-primary)] focus:border-transparent">
                            </div>
                        </div>

                        {{-- Program Pendidikan --}}
                        <div class="lg:col-span-2">
                            <label for="filter_program" class="block text-[11px] font-medium text-slate-600 dark:text-slate-400 mb-1">
                                Program Pendidikan
                            </label>
                            <select id="filter_program" name="program_id"
                                    class="w-full py-2 px-3 text-xs rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-900 text-slate-800 dark:text-slate-100 focus:ring-2 focus:ring-[var(--sabira-primary)]">
                                <option value="">Semua Program</option>
                                @foreach($educationPrograms as $program)
                                    <option value="{{ $program->id }}" {{ (string)$filters['program_id'] === (string)$program->id ? 'selected' : '' }}>
                                        {{ $program->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Jenis Kelas --}}
                        <div class="lg:col-span-2">
                            <label for="filter_class_type" class="block text-[11px] font-medium text-slate-600 dark:text-slate-400 mb-1">
                                Jenis Kelas
                            </label>
                            <select id="filter_class_type" name="class_type"
                                    class="w-full py-2 px-3 text-xs rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-900 text-slate-800 dark:text-slate-100 focus:ring-2 focus:ring-[var(--sabira-primary)]">
                                <option value="">Semua Jenis</option>
                                <option value="reguler" {{ $filters['class_type'] === 'reguler' ? 'selected' : '' }}>Reguler</option>
                                <option value="non_reguler" {{ $filters['class_type'] === 'non_reguler' ? 'selected' : '' }}>Nonreguler</option>
                            </select>
                        </div>

                        {{-- Kelas Aktif / Kelas Asal --}}
                        <div class="lg:col-span-2">
                            <label for="filter_source_class" class="block text-[11px] font-medium text-slate-600 dark:text-slate-400 mb-1">
                                Kelas Aktif / Asal
                            </label>
                            <select id="filter_source_class" name="source_class_group_id"
                                    class="w-full py-2 px-3 text-xs rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-900 text-slate-800 dark:text-slate-100 focus:ring-2 focus:ring-[var(--sabira-primary)]">
                                <option value="">Semua Kelas Asal</option>
                                @foreach($sourceClasses as $sc)
                                    <option value="{{ $sc->id }}" {{ (string)$filters['source_class_group_id'] === (string)$sc->id ? 'selected' : '' }}>
                                        {{ $sc->nama_kelas }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Status Keanggotaan --}}
                        <div class="lg:col-span-2">
                            <label for="filter_membership_status" class="block text-[11px] font-medium text-slate-600 dark:text-slate-400 mb-1">
                                Status Keanggotaan
                            </label>
                            <select id="filter_membership_status" name="membership_status"
                                    class="w-full py-2 px-3 text-xs rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-900 text-slate-800 dark:text-slate-100 focus:ring-2 focus:ring-[var(--sabira-primary)]">
                                <option value="all" {{ $filters['membership_status'] === 'all' ? 'selected' : '' }}>Semua Status</option>
                                <option value="has_active" {{ $filters['membership_status'] === 'has_active' ? 'selected' : '' }}>Memiliki Kelas Aktif</option>
                                <option value="no_active" {{ $filters['membership_status'] === 'no_active' ? 'selected' : '' }}>Belum Memiliki Kelas</option>
                                <option value="in_target" {{ $filters['membership_status'] === 'in_target' ? 'selected' : '' }}>Sudah di Kelas Tujuan</option>
                                <option value="not_in_target" {{ $filters['membership_status'] === 'not_in_target' ? 'selected' : '' }}>Belum di Kelas Tujuan</option>
                            </select>
                        </div>
                    </div>

                    <div class="flex flex-wrap items-center justify-between gap-3 pt-2">
                        <div class="flex flex-wrap items-center gap-4">
                            {{-- Checkbox Hide Target Members --}}
                            <label class="inline-flex items-center gap-2 text-xs font-medium text-slate-700 dark:text-slate-300 cursor-pointer">
                                <input type="checkbox" name="hide_target_members" value="1"
                                       {{ $filters['hide_target_members'] ? 'checked' : '' }}
                                       class="rounded border-slate-300 text-[var(--sabira-primary)] focus:ring-[var(--sabira-primary)]">
                                <span>Sembunyikan siswa yang sudah di kelas tujuan</span>
                            </label>

                            @if($gradeLevels->count() > 0)
                                <div class="inline-flex items-center gap-1.5 text-xs">
                                    <span class="text-slate-500">Tingkat:</span>
                                    <select name="grade_level" class="py-1 px-2 text-xs rounded-lg border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-900">
                                        <option value="">Semua</option>
                                        @foreach($gradeLevels as $gl)
                                            <option value="{{ $gl }}" {{ $filters['grade_level'] === $gl ? 'selected' : '' }}>{{ $gl }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            @endif

                            {{-- Per Page Selector --}}
                            <div class="inline-flex items-center gap-1.5 text-xs">
                                <span class="text-slate-500">Tampilkan:</span>
                                <select name="per_page" class="py-1 px-2 text-xs rounded-lg border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-900">
                                    <option value="25" {{ $filters['per_page'] === 25 ? 'selected' : '' }}>25 per hal</option>
                                    <option value="50" {{ $filters['per_page'] === 50 ? 'selected' : '' }}>50 per hal</option>
                                    <option value="100" {{ $filters['per_page'] === 100 ? 'selected' : '' }}>100 per hal</option>
                                </select>
                            </div>
                        </div>

                        <button type="submit"
                                class="bg-slate-800 hover:bg-slate-900 dark:bg-slate-700 dark:hover:bg-slate-600 text-white text-xs font-semibold px-4 py-2 rounded-xl transition flex items-center gap-1.5">
                            <i class="fas fa-search"></i>
                            <span>Terapkan Filter</span>
                        </button>
                    </div>
                </div>
            </form>

            {{-- Active Filter Chips --}}
            @if($activeCount > 0)
                <div class="mt-4 pt-3 border-t border-slate-200 dark:border-slate-800 flex flex-wrap items-center gap-2">
                    <span class="text-[11px] text-slate-400 font-medium">Filter Aktif:</span>

                    @if($filters['search'])
                        <a href="{{ route('promotion.index', array_merge($filters, ['search' => null])) }}"
                           class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs bg-slate-100 text-slate-700 dark:bg-slate-800 dark:text-slate-300 hover:bg-slate-200">
                            <span>Search: "{{ $filters['search'] }}"</span>
                            <i class="fas fa-times text-[10px] text-slate-400 hover:text-slate-600"></i>
                        </a>
                    @endif

                    @if($filters['program_id'])
                        @php $pName = $educationPrograms->firstWhere('id', (int)$filters['program_id'])?->name; @endphp
                        <a href="{{ route('promotion.index', array_merge($filters, ['program_id' => null])) }}"
                           class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs bg-blue-100 text-blue-800 dark:bg-blue-950 dark:text-blue-300 hover:bg-blue-200">
                            <span>Program: {{ $pName }}</span>
                            <i class="fas fa-times text-[10px] opacity-60"></i>
                        </a>
                    @endif

                    @if($filters['class_type'])
                        <a href="{{ route('promotion.index', array_merge($filters, ['class_type' => null])) }}"
                           class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs bg-indigo-100 text-indigo-800 dark:bg-indigo-950 dark:text-indigo-300 hover:bg-indigo-200">
                            <span>Jenis: {{ ucfirst($filters['class_type']) }}</span>
                            <i class="fas fa-times text-[10px] opacity-60"></i>
                        </a>
                    @endif

                    @if($filters['source_class_group_id'])
                        @php $scName = $sourceClasses->firstWhere('id', (int)$filters['source_class_group_id'])?->nama_kelas; @endphp
                        <a href="{{ route('promotion.index', array_merge($filters, ['source_class_group_id' => null])) }}"
                           class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs bg-emerald-100 text-emerald-800 dark:bg-emerald-950 dark:text-emerald-300 hover:bg-emerald-200">
                            <span>Kelas Asal: {{ $scName }}</span>
                            <i class="fas fa-times text-[10px] opacity-60"></i>
                        </a>
                    @endif

                    @if($filters['membership_status'] && $filters['membership_status'] !== 'all')
                        <a href="{{ route('promotion.index', array_merge($filters, ['membership_status' => 'all'])) }}"
                           class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs bg-amber-100 text-amber-800 dark:bg-amber-950 dark:text-amber-300 hover:bg-amber-200">
                            <span>Status: {{ ucfirst(str_replace('_', ' ', $filters['membership_status'])) }}</span>
                            <i class="fas fa-times text-[10px] opacity-60"></i>
                        </a>
                    @endif

                    @if($filters['hide_target_members'])
                        <a href="{{ route('promotion.index', array_merge($filters, ['hide_target_members' => 0])) }}"
                           class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs bg-purple-100 text-purple-800 dark:bg-purple-950 dark:text-purple-300 hover:bg-purple-200">
                            <span>Sembunyikan Anggota Target</span>
                            <i class="fas fa-times text-[10px] opacity-60"></i>
                        </a>
                    @endif
                </div>
            @endif
        </div>

        {{-- Section 3: Selection Toolbar & Students Table --}}
        <div class="bg-[var(--sabira-surface)] border border-[var(--sabira-neutral-subtle,#e2e8f0)] dark:border-slate-800 rounded-2xl overflow-hidden shadow-sm">

            {{-- Table Toolbar --}}
            <div class="p-4 bg-slate-50/70 dark:bg-slate-900/50 border-b border-slate-200 dark:border-slate-800 flex flex-wrap items-center justify-between gap-3 text-xs">
                <div class="flex items-center gap-3">
                    <label class="inline-flex items-center gap-2 cursor-pointer font-medium text-slate-700 dark:text-slate-300">
                        <input type="checkbox" :checked="isAllPageSelected" @change="toggleSelectAllPage($event.target.checked)"
                               class="rounded border-slate-300 text-[var(--sabira-primary)] focus:ring-[var(--sabira-primary)]">
                        <span>Pilih Semua di Halaman Ini</span>
                    </label>

                    <span class="text-slate-300 dark:text-slate-700">|</span>

                    <span class="text-slate-600 dark:text-slate-400">
                        Menampilkan <strong class="text-slate-900 dark:text-white">{{ $students->firstItem() ?? 0 }}–{{ $students->lastItem() ?? 0 }}</strong> dari <strong class="text-slate-900 dark:text-white">{{ $students->total() }}</strong> siswa
                    </span>
                </div>

                <div class="flex items-center gap-3">
                    <span x-show="selectedIds.length > 0" class="font-bold text-[var(--sabira-primary)] dark:text-blue-400 bg-blue-50 dark:bg-blue-950/60 px-3 py-1 rounded-full border border-blue-200 dark:border-blue-800">
                        <i class="fas fa-check-square"></i> <span x-text="selectedIds.length"></span> Siswa Dipilih
                    </span>
                </div>
            </div>

            {{-- Table View --}}
            @if($students->count() > 0)
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs text-slate-700 dark:text-slate-300">
                        <thead class="bg-slate-100/80 dark:bg-slate-800/80 text-slate-600 dark:text-slate-400 uppercase tracking-wider font-semibold border-b border-slate-200 dark:border-slate-800">
                            <tr>
                                <th class="w-12 px-4 py-3 text-center">
                                    <input type="checkbox" :checked="isAllPageSelected" @change="toggleSelectAllPage($event.target.checked)"
                                           class="rounded border-slate-300 text-[var(--sabira-primary)] focus:ring-[var(--sabira-primary)]">
                                </th>
                                <th class="px-4 py-3">Nama Siswa</th>
                                <th class="px-4 py-3">NIS</th>
                                <th class="px-4 py-3">Program</th>
                                <th class="px-4 py-3">Kelas Aktif Saat Ini</th>
                                <th class="px-4 py-3">Jenis Kelas</th>
                                <th class="px-4 py-3 text-center">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                            @foreach($students as $student)
                                @php
                                    $activeClasses = $student->activeClassGroups;
                                    $isInTargetClass = $filters['to_class_id'] && $activeClasses->pluck('id')->contains((int)$filters['to_class_id']);
                                    $hasMultiple = $activeClasses->count() > 1;
                                @endphp
                                <tr class="hover:bg-slate-50/80 dark:hover:bg-slate-800/50 transition"
                                    :class="isSelected({{ $student->id }}) ? 'bg-blue-50/50 dark:bg-blue-950/30' : ''">

                                    <td class="px-4 py-3 text-center">
                                        <input type="checkbox" value="{{ $student->id }}"
                                               :checked="isSelected({{ $student->id }})"
                                               @change="toggleSelect({{ $student->id }})"
                                               class="rounded border-slate-300 text-[var(--sabira-primary)] focus:ring-[var(--sabira-primary)]">
                                    </td>

                                    <td class="px-4 py-3 font-semibold text-slate-900 dark:text-white">
                                        {{ $student->nama_lengkap }}
                                    </td>

                                    <td class="px-4 py-3 font-mono text-slate-500 dark:text-slate-400">
                                        {{ $student->nis }}
                                    </td>

                                    <td class="px-4 py-3">
                                        @if($activeClasses->count() > 0)
                                            @foreach($activeClasses->pluck('educationProgram.name')->filter()->unique() as $progName)
                                                <span class="inline-block px-2 py-0.5 rounded text-[11px] font-medium bg-blue-100 text-blue-800 dark:bg-blue-950 dark:text-blue-300">
                                                    {{ $progName }}
                                                </span>
                                            @endforeach
                                        @else
                                            <span class="text-slate-400 italic">-</span>
                                        @endif
                                    </td>

                                    <td class="px-4 py-3">
                                        @if($activeClasses->count() > 0)
                                            <div class="flex flex-wrap gap-1 items-center">
                                                @foreach($activeClasses->take(2) as $cg)
                                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[11px] font-medium bg-slate-100 text-slate-800 dark:bg-slate-800 dark:text-slate-200 border border-slate-200 dark:border-slate-700">
                                                        <span>{{ $cg->nama_kelas }}</span>
                                                    </span>
                                                @endforeach
                                                @if($activeClasses->count() > 2)
                                                    <span class="text-[10px] text-slate-500 font-semibold bg-slate-200 dark:bg-slate-700 px-1.5 py-0.5 rounded-full"
                                                          title="{{ $activeClasses->pluck('nama_kelas')->join(', ') }}">
                                                        +{{ $activeClasses->count() - 2 }} kelas
                                                    </span>
                                                @endif
                                            </div>
                                        @else
                                            <span class="text-slate-400 italic">Belum ada kelas</span>
                                        @endif
                                    </td>

                                    <td class="px-4 py-3">
                                        @if($activeClasses->count() > 0)
                                            @foreach($activeClasses->pluck('class_type')->unique() as $ct)
                                                <span class="inline-block px-2 py-0.5 rounded text-[11px] font-medium capitalize {{ $ct === 'reguler' ? 'bg-emerald-100 text-emerald-800 dark:bg-emerald-950 dark:text-emerald-300' : 'bg-purple-100 text-purple-800 dark:bg-purple-950 dark:text-purple-300' }}">
                                                    {{ $ct }}
                                                </span>
                                            @endforeach
                                        @else
                                            <span class="text-slate-400 italic">-</span>
                                        @endif
                                    </td>

                                    <td class="px-4 py-3 text-center">
                                        @if($isInTargetClass)
                                            <x-status-badge status="active" size="sm" />
                                            <span class="block text-[10px] text-emerald-600 dark:text-emerald-400 font-semibold mt-0.5">Sudah di Target</span>
                                        @elseif($activeClasses->count() > 0)
                                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-semibold bg-emerald-50 text-emerald-700 dark:bg-emerald-950/60 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800">
                                                <i class="fas fa-check text-[9px]"></i> Aktif
                                            </span>
                                        @else
                                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-semibold bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-400">
                                                Belum Ada Kelas
                                            </span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                {{-- Pagination Footer --}}
                <div class="p-4 border-t border-slate-200 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-900/50">
                    {{ $students->links() }}
                </div>
            @else
                <div class="p-12 text-center">
                    <x-empty-state icon="fa-user-slash"
                                   title="Siswa Tidak Ditemukan"
                                   description="Tidak ada data siswa yang cocok dengan filter atau kata kunci pencarian yang diterapkan." />
                    <div class="mt-4">
                        <a href="{{ route('promotion.index', array_filter(['to_class_id' => $filters['to_class_id']])) }}"
                           class="inline-flex items-center gap-1.5 text-xs text-[var(--sabira-primary)] hover:underline font-semibold">
                            <i class="fas fa-undo"></i>
                            <span>Bersihkan Seluruh Filter</span>
                        </a>
                    </div>
                </div>
            @endif
        </div>

        {{-- Section 4: Modal Preview & Konfirmasi --}}
        <div x-show="previewModalOpen" x-cloak
             class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4"
             @keydown.escape.window="previewModalOpen = false">

            <div class="bg-[var(--sabira-surface)] border border-slate-200 dark:border-slate-800 rounded-2xl max-w-lg w-full p-6 shadow-2xl space-y-5"
                 @click.away="previewModalOpen = false">

                <div class="flex items-center justify-between">
                    <h3 class="text-base font-bold text-slate-900 dark:text-white flex items-center gap-2">
                        <i class="fas fa-clipboard-check text-[var(--sabira-primary)]"></i>
                        <span>Konfirmasi Proses Keanggotaan</span>
                    </h3>
                    <button type="button" @click="previewModalOpen = false" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 text-sm">
                        <i class="fas fa-times"></i>
                    </button>
                </div>

                <div class="space-y-3 text-xs bg-slate-50 dark:bg-slate-900 p-4 rounded-xl border border-slate-200 dark:border-slate-800">
                    <div class="flex justify-between border-b border-slate-200 dark:border-slate-800 pb-2">
                        <span class="text-slate-500" x-text="actionMode === 'invalidate' ? 'Kelas:' : 'Kelas Tujuan:'"></span>
                        <strong class="text-slate-900 dark:text-white font-bold" x-text="targetClassName"></strong>
                    </div>
                    <div class="flex justify-between border-b border-slate-200 dark:border-slate-800 pb-2">
                        <span class="text-slate-500">Mode Tindakan:</span>
                        <span class="font-semibold px-2 py-0.5 rounded text-[11px]"
                              :class="actionMode === 'invalidate' ? 'bg-rose-100 text-rose-800' : (actionMode === 'transfer' ? 'bg-indigo-100 text-indigo-800' : 'bg-emerald-100 text-emerald-800')"
                              x-text="actionMode === 'invalidate' ? 'Batalkan Keanggotaan' : (actionMode === 'transfer' ? 'Pindahkan Kelas Reguler' : 'Tambahkan ke Kelas')"></span>
                    </div>
                    <div class="flex justify-between border-b border-slate-200 dark:border-slate-800 pb-2">
                        <span class="text-slate-500">Total Siswa Dipilih:</span>
                        <strong class="text-slate-900 dark:text-white" x-text="selectedIds.length + ' siswa'"></strong>
                    </div>
                    <div class="flex justify-between text-slate-600 dark:text-slate-400">
                        <span x-text="actionMode === 'invalidate' ? 'Membership sudah berubah/tidak aktif:' : 'Dilewati (Sudah di Kelas Tujuan):'"></span>
                        <span class="font-medium text-amber-600 dark:text-amber-400" x-text="actionMode === 'invalidate' ? previewStats.stale + ' siswa' : countAlreadyInTarget + ' siswa'"></span>
                    </div>
                    <div class="flex justify-between text-slate-600 dark:text-slate-400">
                        <span>Akan Diproses:</span>
                        <strong class="text-emerald-600 dark:text-emerald-400 font-bold" x-text="actionMode === 'invalidate' ? previewStats.valid + ' siswa' : countEligibleToProcess + ' siswa'"></strong>
                    </div>
                    <div x-show="actionMode === 'invalidate'" class="flex justify-between text-slate-600 dark:text-slate-400">
                        <span>Memiliki histori attendance:</span><strong class="text-amber-600" x-text="previewStats.attendance_history + ' siswa'"></strong>
                    </div>
                    <div x-show="actionMode === 'invalidate'" class="pt-2 border-t border-slate-200"><span class="text-slate-500">Alasan:</span> <span x-text="invalidationReason"></span></div>
                </div>

                <div x-show="actionMode === 'transfer'" class="p-3 bg-amber-50 dark:bg-amber-950/40 border border-amber-200 dark:border-amber-800 rounded-xl text-[11px] text-amber-800 dark:text-amber-300">
                    <i class="fas fa-info-circle mr-1"></i> Mode pindah akan menutup keanggotaan reguler lama siswa pada program & tahun ajaran yang sama dan mencatat tanggal keluar (histori tetap tersimpan).
                </div>

                {{-- Actual Form Submission --}}
                <form method="POST" action="{{ route('promotion.promote') }}" class="space-y-4">
                    @csrf
                    <input type="hidden" name="to_class_id" :value="toClassId">
                    <input type="hidden" name="action_mode" :value="actionMode">
                    <input type="hidden" name="invalidation_reason" :value="invalidationReason">

                    <template x-for="id in selectedIds" :key="id">
                        <input type="hidden" name="student_ids[]" :value="id">
                    </template>

                    <div class="flex items-center justify-end gap-3 pt-2 border-t border-slate-200 dark:border-slate-800">
                        <button type="button" @click="previewModalOpen = false"
                                class="px-4 py-2 text-xs rounded-xl border border-slate-300 dark:border-slate-700 hover:bg-slate-100 dark:hover:bg-slate-800 text-slate-700 dark:text-slate-300 font-medium">
                            Batal
                        </button>

                        <button type="submit"
                                class="bg-[var(--sabira-primary)] hover:bg-[var(--sabira-primary-active,#1e40af)] text-white text-xs font-bold px-5 py-2.5 rounded-xl shadow transition flex items-center gap-2">
                            <i class="fas fa-check-circle"></i>
                            <span x-text="submitButtonText"></span>
                        </button>
                    </div>
                </form>
            </div>
        </div>

    </div>

    {{-- Alpine JS Promotion Manager script --}}
    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('promotionManager', (config) => ({
                allPageStudentIds: config.allPageStudentIds || [],
                studentsData: config.studentsData || [],
                toClassId: config.toClassId || '',
                targetClassType: config.targetClassType || 'reguler',
                targetClassName: config.targetClassName || '',
                actionMode: config.actionMode || 'add',
                invalidationReason: '',
                previewUrl: config.previewUrl,
                csrfToken: config.csrfToken,
                previewStats: {selected: 0, valid: 0, stale: 0, attendance_history: 0},
                selectedIds: [],
                mobileFiltersOpen: false,
                previewModalOpen: false,

                init() {
                    const storageKey = 'sabira_promotion_selected_ids';
                    const stored = sessionStorage.getItem(storageKey);
                    if (stored) {
                        try {
                            this.selectedIds = JSON.parse(stored);
                        } catch (e) {
                            this.selectedIds = [];
                        }
                    }

                    this.$watch('selectedIds', (newVal) => {
                        sessionStorage.setItem(storageKey, JSON.stringify(newVal));
                    });

                    if (this.targetClassType === 'non_reguler') {
                        this.actionMode = 'add';
                    }
                },

                onTargetClassChange(event) {
                    const select = event.target;
                    const opt = select.selectedOptions[0];
                    if (opt) {
                        this.targetClassType = opt.getAttribute('data-[class-type]') || 'reguler';
                        this.targetClassName = opt.getAttribute('data-[class-name]') || '';
                        if (this.targetClassType === 'non_reguler') {
                            this.actionMode = 'add';
                        }
                    } else {
                        this.targetClassType = 'reguler';
                        this.targetClassName = '';
                    }
                    document.getElementById('filter-form')?.requestSubmit();
                },

                onActionModeChange() {
                    this.clearSelections();
                    document.getElementById('filter-form')?.requestSubmit();
                },

                get actionModeDescription() {
                    if (this.actionMode === 'add') {
                        return 'Menambahkan siswa sebagai anggota kelas tanpa menutup keanggotaan kelas reguler/nonreguler yang sudah ada.';
                    } else if (this.actionMode === 'transfer') {
                        return 'Memindahkan siswa kelas reguler dengan menutup keanggotaan reguler lama pada program dan TA yang sama.';
                    }
                    return 'Menandai membership salah input sebagai Entered in Error tanpa menghapus histori membership maupun attendance.';
                },

                isSelected(id) {
                    return this.selectedIds.includes(Number(id));
                },

                toggleSelect(id) {
                    const numId = Number(id);
                    if (this.isSelected(numId)) {
                        this.selectedIds = this.selectedIds.filter(i => i !== numId);
                    } else {
                        this.selectedIds.push(numId);
                    }
                },

                get isAllPageSelected() {
                    if (this.allPageStudentIds.length === 0) return false;
                    return this.allPageStudentIds.every(id => this.selectedIds.includes(Number(id)));
                },

                toggleSelectAllPage(checked) {
                    if (checked) {
                        this.allPageStudentIds.forEach(id => {
                            const numId = Number(id);
                            if (!this.selectedIds.includes(numId)) {
                                this.selectedIds.push(numId);
                            }
                        });
                    } else {
                        this.selectedIds = this.selectedIds.filter(id => !this.allPageStudentIds.map(Number).includes(id));
                    }
                },

                clearSelections() {
                    this.selectedIds = [];
                    sessionStorage.removeItem('sabira_promotion_selected_ids');
                },

                get countAlreadyInTarget() {
                    if (!this.toClassId) return 0;
                    return this.studentsData.filter(st => {
                        if (!this.selectedIds.includes(Number(st.id))) return false;
                        const activeGroups = st.active_class_groups || [];
                        return activeGroups.some(cg => Number(cg.id) === Number(this.toClassId));
                    }).length;
                },

                get countEligibleToProcess() {
                    return Math.max(0, this.selectedIds.length - this.countAlreadyInTarget);
                },

                get submitButtonText() {
                    const count = this.selectedIds.length;
                    if (this.actionMode === 'transfer') {
                        return `Pindahkan ${count} Siswa`;
                    }
                    if (this.actionMode === 'invalidate') {
                        return `Batalkan Keanggotaan ${count} Siswa`;
                    }
                    return `Tambahkan ${count} Siswa`;
                },

                async openPreviewModal() {
                    if (!this.toClassId) {
                        alert('Silakan pilih Kelas Tujuan terlebih dahulu.');
                        return;
                    }
                    if (this.selectedIds.length === 0) {
                        alert('Silakan pilih minimal 1 siswa.');
                        return;
                    }
                    if (this.actionMode === 'invalidate') {
                        if (this.invalidationReason.trim().length < 5) {
                            alert('Alasan Pembatalan wajib diisi minimal 5 karakter.');
                            return;
                        }
                        const body = new FormData();
                        body.append('_token', this.csrfToken);
                        body.append('to_class_id', this.toClassId);
                        body.append('action_mode', 'invalidate');
                        body.append('invalidation_reason', this.invalidationReason);
                        this.selectedIds.forEach(id => body.append('student_ids[]', id));
                        const response = await fetch(this.previewUrl, {method: 'POST', body, headers: {'Accept': 'application/json'}});
                        if (!response.ok) {
                            alert('Preview tidak dapat dihitung ulang. Periksa pilihan dan alasan.');
                            return;
                        }
                        this.previewStats = await response.json();
                    }
                    this.previewModalOpen = true;
                }
            }));
        });
    </script>
</x-app-shell>
