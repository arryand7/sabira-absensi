<x-app-shell headerTitle="Integrasi & Sinkronisasi User Gate SSO" headerSubtitle="Platform Rekonsiliasi Identity Engine 8 Kategori">
    <div class="space-y-6">

        @if(session('success'))<div class="rounded-xl border border-emerald-200 bg-emerald-50 p-4 text-sm text-emerald-800">{{ session('success') }}</div>@endif
        @if(session('error'))<div class="rounded-xl border border-rose-200 bg-rose-50 p-4 text-sm text-rose-800">{{ session('error') }}</div>@endif

        <!-- Top Header & Dry-Run Trigger Card -->
        <div class="rounded-[var(--radius-md)] border border-[var(--sabira-border-soft)] bg-[var(--sabira-surface)] p-6 text-[var(--sabira-ink)]">
            <div class="flex flex-col md:flex-row items-start md:items-center justify-between gap-6">
                <div class="flex items-center gap-4">
                    <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-[var(--sabira-primary)]/30 text-indigo-400 border border-indigo-500/30">
                        <i class="fas fa-sync text-2xl"></i>
                    </div>
                    <div>
                        <h2 class="text-lg font-bold">Gate SSO Provisioning Hub</h2>
                        <p class="text-xs text-indigo-200/80 mt-0.5">Ambil snapshot data user dari Gate SSO, jalankan dry-run preview 8 kategori, dan terapkan perubahan secara aman.</p>
                        <p class="mt-2 text-[11px] {{ config('services.gate.client_id') && config('services.gate.client_secret') ? 'text-emerald-300' : 'text-amber-300' }}">
                            <i class="fas fa-circle mr-1 text-[7px]"></i>
                            {{ config('services.gate.client_id') && config('services.gate.client_secret') ? 'Kredensial provisioning terkonfigurasi' : 'Kredensial provisioning belum lengkap' }}
                        </p>
                    </div>
                </div>

                <form action="{{ route('admin.sync.preview') }}" method="POST" class="w-full md:w-auto">
                    @csrf
                    <button type="submit" class="w-full md:w-auto inline-flex items-center justify-center gap-2 rounded-xl bg-[var(--sabira-primary)] px-6 py-3 text-xs font-bold text-white  hover:bg-[var(--sabira-primary-active)] transition">
                        <i class="fas fa-play text-xs"></i> <span>Jalankan Dry-Run Preview</span>
                    </button>
                </form>
            </div>
        </div>

        <!-- Tabel Riwayat Sync Run -->
        <div class="rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 p-6 shadow-sm">
            <div class="flex items-center justify-between mb-4 border-b border-slate-100 dark:border-slate-800 pb-3">
                <h3 class="text-sm font-bold text-slate-900 dark:text-white uppercase tracking-wider">Riwayat Sesi Sinkronisasi</h3>
                <span class="text-xs text-slate-400">Pull-Based Provisioning Log</span>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full text-xs text-left">
                    <thead class="bg-slate-50 dark:bg-slate-800 text-slate-500 font-bold uppercase border-b border-slate-200 dark:border-slate-700">
                        <tr>
                            <th class="px-4 py-3">UUID Sesi</th>
                            <th class="px-4 py-3">Inisiator</th>
                            <th class="px-4 py-3">Status Run</th>
                            <th class="px-4 py-3">Waktu Preview</th>
                            <th class="px-4 py-3">Waktu Diterapkan</th>
                            <th class="px-4 py-3">Ringkasan Item</th>
                            <th class="px-4 py-3 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                        @forelse($runs as $run)
                            <tr class="hover:bg-slate-50/60 dark:hover:bg-slate-800/40">
                                <td class="px-4 py-3 font-mono font-bold text-indigo-600 dark:text-indigo-400">
                                    {{ Str::limit($run->uuid, 8) }}
                                </td>
                                <td class="px-4 py-3 font-semibold text-slate-900 dark:text-white">
                                    {{ $run->initiator?->name ?? 'Sistem Automation' }}
                                </td>
                                <td class="px-4 py-3">
                                    <x-status-badge :status="$run->status" size="sm" />
                                </td>
                                <td class="px-4 py-3 text-slate-500">
                                    {{ $run->previewed_at?->format('d M Y H:i') ?? '-' }}
                                </td>
                                <td class="px-4 py-3 text-slate-500">
                                    {{ $run->applied_at?->format('d M Y H:i') ?? '-' }}
                                </td>
                                <td class="px-4 py-3 space-y-0.5">
                                    @if($run->summary_counts)
                                        <div class="flex items-center gap-2 text-[11px]">
                                            <span class="text-emerald-600 font-bold">New: {{ $run->summary_counts['missing_in_application'] ?? 0 }}</span>
                                            <span class="text-amber-600 font-bold">Update: {{ $run->summary_counts['needs_update'] ?? 0 }}</span>
                                            <span class="text-rose-600 font-bold">Revoked: {{ $run->summary_counts['access_revoked'] ?? 0 }}</span>
                                        </div>
                                    @else
                                        <span class="text-slate-400">-</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-right">
                                    <a href="{{ route('admin.sync.show', $run->id) }}" class="inline-flex items-center gap-1 rounded-lg bg-indigo-50 dark:bg-indigo-950/60 px-3 py-1.5 text-xs font-bold text-indigo-600 dark:text-indigo-400 hover:bg-indigo-100 transition">
                                        <span>Detail Rekonsiliasi</span> <i class="fas fa-chevron-right text-[10px]"></i>
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-4 py-8 text-center">
                                    <x-empty-state title="Belum Ada Riwayat Sinkronisasi" description="Jalankan dry-run preview pertama untuk memulai rekonsiliasi akun pengguna dari Gate SSO." icon="fas fa-sync" />
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-4">
                {{ $runs->links() }}
            </div>
        </div>
    </div>
</x-app-shell>
