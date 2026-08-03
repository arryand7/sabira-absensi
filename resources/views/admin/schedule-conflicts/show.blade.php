<x-app-shell header-title="Review Benturan Jadwal" header-subtitle="Bandingkan kedua jadwal sebelum mengambil keputusan">
    @php
        $conflict = $scheduleConflict;
        $first = $conflict->schedule;
        $second = $conflict->conflictingSchedule;
        $overlapStart = max(substr($first->jam_mulai, 0, 5), substr($second->jam_mulai, 0, 5));
        $overlapEnd = min(substr($first->jam_selesai, 0, 5), substr($second->jam_selesai, 0, 5));
    @endphp
    <div class="space-y-6" x-data="{ resolution: '', decisionTitle: '' }">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <x-button variant="secondary" :href="route('admin.schedule-conflicts.index')"><i class="fas fa-arrow-left"></i> Kembali</x-button>
            <x-status-badge :status="str_replace('_', ' ', $conflict->status)" />
        </div>

        <x-alert type="warning" title="Waktu bertumpang tindih">
            {{ $first->hari }}, {{ $overlapStart }}–{{ $overlapEnd }} · {{ $conflict->conflict_type === 'teacher_overlap' ? 'Guru yang sama memiliki dua jadwal.' : 'Kelas yang sama memiliki dua jadwal.' }}
        </x-alert>

        <section class="grid gap-4 lg:grid-cols-[1fr_auto_1fr] lg:items-stretch">
            @foreach([['label' => 'Jadwal baru/diubah', 'schedule' => $first], ['label' => 'Jadwal pembanding', 'schedule' => $second]] as $index => $item)
                @if($index === 1)<div class="hidden items-center justify-center lg:flex"><span class="flex h-11 w-11 items-center justify-center rounded-full border border-[var(--sabira-border)] bg-[var(--sabira-surface-soft)] text-[var(--sabira-warning)]"><i class="fas fa-code-compare"></i></span></div>@endif
                <article class="sabira-card {{ $item['schedule']->trashed() ? 'opacity-60' : '' }}">
                    <p class="text-xs font-semibold text-[var(--sabira-muted)]">{{ $item['label'] }}</p>
                    <h2 class="mt-2 text-xl font-semibold text-[var(--sabira-ink)]">{{ $item['schedule']->subject?->nama_mapel }}</h2>
                    <dl class="mt-5 grid grid-cols-2 gap-4 text-sm">
                        <div><dt class="text-[var(--sabira-muted)]">Guru</dt><dd class="font-medium text-[var(--sabira-ink)]">{{ $item['schedule']->user?->name }}</dd></div>
                        <div><dt class="text-[var(--sabira-muted)]">Kelas</dt><dd class="font-medium text-[var(--sabira-ink)]">{{ $item['schedule']->classGroup?->nama_kelas }}</dd></div>
                        <div><dt class="text-[var(--sabira-muted)]">Hari</dt><dd>{{ $item['schedule']->hari }}</dd></div>
                        <div><dt class="text-[var(--sabira-muted)]">Waktu</dt><dd>{{ substr($item['schedule']->jam_mulai, 0, 5) }}–{{ substr($item['schedule']->jam_selesai, 0, 5) }}</dd></div>
                        <div><dt class="text-[var(--sabira-muted)]">Tahun</dt><dd>{{ $item['schedule']->academicYear?->name }}</dd></div>
                        <div><dt class="text-[var(--sabira-muted)]">Semester</dt><dd>{{ ucfirst($item['schedule']->semester) }}</dd></div>
                    </dl>
                    @if($item['schedule']->trashed())<x-alert type="danger" class="mt-4">Jadwal ini sudah dinonaktifkan.</x-alert>@endif
                </article>
            @endforeach
        </section>

        @if($conflict->isPending())
            <section class="sabira-card">
                <h2 class="sabira-card-title">Keputusan admin</h2>
                <p class="sabira-card-subtitle">Jadwal yang tidak dipertahankan akan dinonaktifkan dengan soft delete agar histori sesi tetap aman.</p>
                <div class="mt-5 grid gap-3 sm:grid-cols-2 xl:grid-cols-4">
                    <button type="button" class="sabira-button sabira-button-primary" @click="resolution='keep_current'; decisionTitle='Pertahankan jadwal baru dan nonaktifkan jadwal pembanding'; $dispatch('open-modal', 'resolve-schedule-conflict')">Pertahankan Jadwal Baru</button>
                    <button type="button" class="sabira-button sabira-button-secondary" @click="resolution='keep_existing'; decisionTitle='Pertahankan jadwal pembanding dan nonaktifkan jadwal baru'; $dispatch('open-modal', 'resolve-schedule-conflict')">Pertahankan Jadwal Lama</button>
                    <button type="button" class="sabira-button sabira-button-secondary" @click="resolution='keep_both'; decisionTitle='Verifikasi dan biarkan kedua jadwal tetap aktif'; $dispatch('open-modal', 'resolve-schedule-conflict')">Verifikasi Keduanya</button>
                    <button type="button" class="sabira-button sabira-button-tertiary" @click="resolution='dismiss'; decisionTitle='Tandai sebagai false positive'; $dispatch('open-modal', 'resolve-schedule-conflict')">Dismiss</button>
                </div>
            </section>

            <x-modal name="resolve-schedule-conflict" maxWidth="md" focusable>
                <form method="POST" action="{{ route('admin.schedule-conflicts.resolve', $conflict) }}" class="p-6">
                    @csrf
                    <input type="hidden" name="resolution" x-model="resolution">
                    <h2 class="sabira-card-title" x-text="decisionTitle"></h2>
                    <p class="sabira-card-subtitle mt-2">Periksa keputusan sebelum melanjutkan. Tindakan penonaktifan tetap tercatat dan histori pembelajaran tidak dihapus.</p>
                    <x-form-field label="Catatan verifikasi" name="resolution_note" class="mt-5">
                        <x-textarea name="resolution_note" placeholder="Alasan atau catatan keputusan admin...">{{ old('resolution_note') }}</x-textarea>
                    </x-form-field>
                    <div class="mt-6 flex justify-end gap-3"><x-button variant="secondary" type="button" @click="$dispatch('close-modal', 'resolve-schedule-conflict')">Batal</x-button><x-button type="submit">Simpan Keputusan</x-button></div>
                </form>
            </x-modal>
        @else
            <section class="sabira-card"><h2 class="sabira-card-title">Audit keputusan</h2><p class="mt-3 text-sm text-[var(--sabira-body)]">Diputuskan oleh {{ $conflict->resolver?->name ?? 'Sistem' }} pada {{ $conflict->resolved_at?->format('d/m/Y H:i') }}.</p><p class="mt-2 text-sm text-[var(--sabira-muted)]">{{ $conflict->resolution_note ?: 'Tidak ada catatan.' }}</p></section>
        @endif
    </div>
</x-app-shell>
