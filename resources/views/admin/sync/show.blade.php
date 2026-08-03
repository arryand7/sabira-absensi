<x-app-shell headerTitle="Detail Rekonsiliasi Gate SSO" headerSubtitle="Sesi Run UUID: {{ Str::limit($run->uuid, 12) }}">
    <div class="space-y-6" x-data="{ activeTab: 'all' }">

        @if(session('success'))<div class="rounded-xl border border-emerald-200 bg-emerald-50 p-4 text-sm text-emerald-800">{{ session('success') }}</div>@endif
        @if(session('error'))<div class="rounded-xl border border-rose-200 bg-rose-50 p-4 text-sm text-rose-800">{{ session('error') }}</div>@endif
        @if($errors->any())<div class="rounded-xl border border-rose-200 bg-rose-50 p-4 text-sm text-rose-800">{{ $errors->first() }}</div>@endif

        <!-- Top Action Bar & Header -->
        <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 p-6 rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 shadow-sm">
            <div>
                <div class="flex items-center gap-3">
                    <a href="{{ route('admin.sync.index') }}" class="flex h-9 w-9 items-center justify-center rounded-xl bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 hover:bg-slate-200 transition">
                        <i class="fas fa-arrow-left text-sm"></i>
                    </a>
                    <div>
                        <h2 class="text-base font-bold text-slate-900 dark:text-white">Rekonsiliasi User Gate SSO</h2>
                        <p class="text-xs text-slate-500">Run ID #{{ $run->id }} • {{ $run->previewed_at?->format('d M Y H:i') ?? '-' }}</p>
                    </div>
                </div>
            </div>

            <div class="flex items-center gap-3">
                @if($run->status === 'previewed')
                    @php
                        $changeCount = ($run->summary_counts['missing_in_application'] ?? 0) 
                            + ($run->summary_counts['needs_update'] ?? 0) 
                            + ($run->summary_counts['access_revoked'] ?? 0) 
                            + ($run->summary_counts['inactive_in_gate'] ?? 0)
                            + ($run->summary_counts['reactivation_required'] ?? 0);
                    @endphp
                    <form action="{{ route('admin.sync.apply', $run->id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menerapkan {{ $changeCount }} perubahan ini ke database lokal?')">
                        @csrf
                        <button type="submit" class="sabira-button sabira-button-primary px-6">
                            <i class="fas fa-check-circle text-xs"></i>
                            <span>Terapkan {{ $changeCount }} Perubahan</span>
                        </button>
                    </form>
                @elseif($run->status === 'report_pending')
                    <form action="{{ route('admin.sync.retry-report', $run) }}" method="POST">
                        @csrf
                        <button type="submit" class="inline-flex items-center gap-2 rounded-xl bg-amber-600 px-6 py-3 text-xs font-bold text-white shadow-lg hover:bg-amber-500">
                            <i class="fas fa-rotate"></i> Kirim Ulang Laporan ke Gate
                        </button>
                    </form>
                @endif
            </div>
        </div>

        <!-- DESIGN.md Section 10: 8-Category Reconciliation Cards Grid -->
        <div class="grid grid-cols-2 sm:grid-cols-4 lg:grid-cols-8 gap-3">
            @php
                $categories = [
                    'matched' => ['label' => 'Matched', 'color' => 'emerald', 'count' => $run->summary_counts['matched'] ?? 0],
                    'needs_update' => ['label' => 'Needs Update', 'color' => 'amber', 'count' => $run->summary_counts['needs_update'] ?? 0],
                    'missing_in_application' => ['label' => 'Missing (App)', 'color' => 'blue', 'count' => $run->summary_counts['missing_in_application'] ?? 0],
                    'access_revoked' => ['label' => 'Access Revoked', 'color' => 'rose', 'count' => $run->summary_counts['access_revoked'] ?? 0],
                    'inactive_in_gate' => ['label' => 'Inactive Gate', 'color' => 'rose', 'count' => $run->summary_counts['inactive_in_gate'] ?? 0],
                    'reactivation_required' => ['label' => 'Reactivate', 'color' => 'purple', 'count' => $run->summary_counts['reactivation_required'] ?? 0],
                    'local_only' => ['label' => 'Local Only', 'color' => 'slate', 'count' => $run->summary_counts['local_only'] ?? 0],
                    'conflict' => ['label' => 'Conflict', 'color' => 'indigo', 'count' => $run->summary_counts['conflict'] ?? 0],
                ];
            @endphp

            @foreach($categories as $catKey => $cat)
                <button @click="activeTab = '{{ $catKey }}'" class="flex flex-col items-center justify-center p-3 rounded-xl border text-center transition-all" :class="activeTab === '{{ $catKey }}' ? 'bg-[var(--sabira-primary)] text-white border-indigo-600 shadow-md ring-2 ring-indigo-300' : 'bg-white dark:bg-slate-900 border-slate-200 dark:border-slate-800 text-slate-700 dark:text-slate-300 hover:border-indigo-300'">
                    <span class="text-[10px] font-bold uppercase tracking-wider opacity-80">{{ $cat['label'] }}</span>
                    <span class="text-xl font-extrabold mt-1">{{ $cat['count'] }}</span>
                </button>
            @endforeach
        </div>

        <!-- Filter Tab Controller Bar -->
        <div class="flex items-center gap-2 border-b border-slate-200 dark:border-slate-800 pb-2 overflow-x-auto text-xs font-semibold">
            <button @click="activeTab = 'all'" class="px-4 py-2 rounded-lg transition" :class="activeTab === 'all' ? 'bg-[var(--sabira-primary)] text-white font-bold' : 'text-slate-500 hover:bg-slate-100 dark:hover:bg-slate-800'">
                Semua Kategori ({{ $run->items->count() }})
            </button>
            @foreach($categories as $catKey => $cat)
                <button @click="activeTab = '{{ $catKey }}'" class="px-3 py-2 rounded-lg transition" :class="activeTab === '{{ $catKey }}' ? 'bg-[var(--sabira-primary)] text-white font-bold' : 'text-slate-500 hover:bg-slate-100 dark:hover:bg-slate-800'">
                    {{ $cat['label'] }} ({{ $cat['count'] }})
                </button>
            @endforeach
        </div>

        <!-- Table of Items -->
        <div class="rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 p-6 shadow-sm">
            @if($run->status === 'previewed')
                <form action="{{ route('admin.sync.actions.update', $run) }}" method="POST">
                    @csrf
                    @method('PUT')
            @endif
            <div class="overflow-x-auto">
                <table class="min-w-full text-xs text-left">
                    <thead class="bg-slate-50 dark:bg-slate-800 text-slate-500 font-bold uppercase border-b border-slate-200 dark:border-slate-700">
                        <tr>
                            <th class="px-4 py-3">Kategori</th>
                            <th class="px-4 py-3">Gate User (UUID / Email)</th>
                            <th class="px-4 py-3">Local User</th>
                            <th class="px-4 py-3">Rencana Aksi</th>
                            <th class="px-4 py-3">Status Hasil</th>
                            <th class="px-4 py-3">Keterangan / Perbedaan</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                        @forelse($run->items as $item)
                            <tr x-show="activeTab === 'all' || activeTab === '{{ $item->category }}'" class="hover:bg-slate-50/60 dark:hover:bg-slate-800/40">
                                <td class="px-4 py-3">
                                    <x-status-badge :status="$item->category" size="sm" />
                                </td>
                                <td class="px-4 py-3">
                                    @if($item->gate_snapshot)
                                        <p class="font-bold text-slate-900 dark:text-white">{{ $item->gate_snapshot['name'] ?? '-' }}</p>
                                        <p class="text-slate-400">{{ $item->gate_snapshot['email'] ?? '-' }}</p>
                                        <p class="font-mono text-[10px] text-slate-400"><code>{{ Str::limit($item->gate_user_uuid, 16) }}</code></p>
                                    @else
                                        <span class="text-slate-400">-</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3">
                                    @if($item->localUser)
                                        <p class="font-semibold text-slate-900 dark:text-white">#{{ $item->localUser->id }} - {{ $item->localUser->name }}</p>
                                    @else
                                        <span class="text-slate-400 italic">Belum ada di DB lokal</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 font-mono font-bold text-indigo-600 dark:text-indigo-400">
                                    @php
                                        $actionOptions = match($item->category) {
                                            'needs_update' => ['update_local' => 'Perbarui lokal', 'no_change' => 'Lewati'],
                                            'missing_in_application' => ['create_local' => 'Buat akun lokal', 'no_change' => 'Lewati'],
                                            'access_revoked', 'inactive_in_gate' => ['suspend_local' => 'Suspend lokal', 'no_change' => 'Lewati'],
                                            'reactivation_required' => ['reactivate_local' => 'Aktifkan kembali', 'no_change' => 'Lewati'],
                                            'local_only', 'conflict' => ['manual_review' => 'Review manual', 'no_change' => 'Lewati'],
                                            default => ['no_change' => 'Tidak berubah'],
                                        };
                                    @endphp
                                    @if($run->status === 'previewed')
                                        <select name="actions[{{ $item->id }}]" class="rounded-lg border-slate-300 bg-white py-1.5 text-xs text-slate-800">
                                            @foreach($actionOptions as $value => $label)
                                                <option value="{{ $value }}" @selected($item->selected_action === $value)>{{ $label }}</option>
                                            @endforeach
                                        </select>
                                    @else
                                        {{ str_replace('_', ' ', $item->selected_action) }}
                                    @endif
                                </td>
                                <td class="px-4 py-3">
                                    <x-status-badge :status="$item->result_status ?? 'pending'" size="sm" />
                                </td>
                                <td class="px-4 py-3">
                                    @if($item->category === 'conflict')
                                        <div class="p-2 rounded-lg bg-indigo-50 dark:bg-indigo-950/60 border border-indigo-200 dark:border-indigo-800 text-[11px] text-indigo-900 dark:text-indigo-300 font-medium">
                                            <i class="fas fa-exclamation-circle text-indigo-500 mr-1"></i>
                                            Data ini tidak dapat digabung otomatis. Superadmin harus meninjau dan menghubungkan akun secara manual.
                                        </div>
                                    @elseif($item->field_differences)
                                        <pre class="p-2 rounded-lg bg-slate-100 dark:bg-slate-800 text-[10px] font-mono text-slate-700 dark:text-slate-300 overflow-x-auto max-w-xs">{{ json_encode($item->field_differences, JSON_PRETTY_PRINT) }}</pre>
                                    @else
                                        <span class="text-slate-400 text-[11px]">Sesuai / Tidak ada perbedaan</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-4 py-8 text-center text-slate-400">Tidak ada item dalam sesi rekonsiliasi ini.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($run->status === 'previewed')
                    <div class="mt-4 flex items-center justify-between border-t border-slate-100 pt-4 text-xs text-slate-500">
                        <span>Conflict hanya dapat ditandai untuk review manual atau dilewati; tidak ada auto-merge.</span>
                        <button class="rounded-lg bg-[var(--sabira-primary)] px-4 py-2 font-bold text-white hover:bg-[var(--sabira-primary-active)]">Simpan Pilihan Tindakan</button>
                    </div>
                </form>
            @endif
        </div>
    </div>
</x-app-shell>
