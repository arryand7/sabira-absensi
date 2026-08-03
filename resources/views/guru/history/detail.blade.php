<x-app-shell>
    <div class="py-6 px-4 max-w-5xl mx-auto space-y-6">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <h2 class="text-xl font-bold text-[var(--sabira-ink)]">Detail Sesi Pembelajaran</h2>
                <p class="text-sm text-gray-600">Data final sesi dan riwayat permintaan koreksi.</p>
            </div>
            <a href="{{ route('guru.history.index') }}" class="bg-[var(--sabira-primary)] text-white px-4 py-2 rounded-md text-sm hover:bg-[var(--sabira-primary-active)]">← Kembali</a>
        </div>

        @if(session('success'))
            <div class="rounded-lg border border-green-200 bg-green-50 p-3 text-sm text-green-800">{{ session('success') }}</div>
        @endif

        @if($errors->any())
            <div class="rounded-lg border border-red-200 bg-red-50 p-3 text-sm text-red-800">
                <ul class="list-disc pl-5">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
            </div>
        @endif

        <div class="grid gap-4 rounded-xl border border-[var(--sabira-border)] bg-[var(--sabira-surface-soft)] p-5 text-sm sm:grid-cols-2 lg:grid-cols-4">
            <div><span class="block text-gray-500">Mata pelajaran</span><strong>{{ $session->schedule->subject->nama_mapel }}</strong></div>
            <div><span class="block text-gray-500">Kelas</span><strong>{{ $session->schedule->classGroup->nama_kelas }}</strong></div>
            <div><span class="block text-gray-500">Pertemuan</span><strong>Ke-{{ $session->meeting_no }}</strong></div>
            <div><span class="block text-gray-500">Tanggal</span><strong>{{ \Carbon\Carbon::parse($session->date)->format('d M Y') }}</strong></div>
            <div><span class="block text-gray-500">Guru aktual</span><strong>{{ $session->actualTeacher?->name ?? $session->scheduledTeacher?->name ?? '-' }}</strong></div>
            <div><span class="block text-gray-500">Status lokasi</span><strong>{{ str_replace('_', ' ', ucfirst($session->location_validation_status)) }}</strong></div>
            <div class="sm:col-span-2"><span class="block text-gray-500">Materi</span><strong>{{ $session->attendances->first()?->materi ?? '-' }}</strong></div>
            <div class="sm:col-span-2"><span class="block text-gray-500">Kondisi kelas</span><strong>{{ $session->classroom_condition ?: '-' }}</strong></div>
            <div class="sm:col-span-2"><span class="block text-gray-500">Catatan guru</span><strong>{{ $session->teacher_notes ?: '-' }}</strong></div>
        </div>

        <div class="overflow-x-auto rounded-xl bg-white shadow-sm">
            <table class="min-w-full divide-y divide-gray-200 text-sm">
                <thead class="bg-[var(--sabira-primary)] text-white"><tr><th class="px-4 py-3 text-left">Nama Siswa</th><th class="px-4 py-3 text-left">Status</th></tr></thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach($session->attendances as $attendance)
                        <tr><td class="px-4 py-2">{{ $attendance->student->nama_lengkap }}</td><td class="px-4 py-2 capitalize">{{ str_replace('_', ' ', $attendance->status) }}</td></tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        @php($pendingCorrection = $session->corrections->firstWhere('status', 'pending'))
        @if($pendingCorrection)
            <div class="rounded-xl border border-amber-300 bg-amber-50 p-5">
                <h3 class="font-semibold text-amber-900">Koreksi menunggu peninjauan</h3>
                <p class="mt-1 text-sm text-amber-800">Diajukan {{ $pendingCorrection->created_at->diffForHumans() }}. Alasan: {{ $pendingCorrection->reason }}</p>
            </div>
        @elseif($session->status === 'completed' && $session->attendances->isNotEmpty())
            <details class="rounded-xl border border-[var(--sabira-border)] bg-white p-5" {{ $errors->any() ? 'open' : '' }}>
                <summary class="cursor-pointer font-semibold text-[var(--sabira-ink)]">Ajukan Koreksi Sesi</summary>
                <p class="mt-2 text-sm text-gray-600">Perubahan tidak langsung diterapkan. Admin harus meninjau dan menyetujuinya.</p>
                <form method="POST" action="{{ route('guru.history.correction.store', $session) }}" class="mt-5 space-y-4">
                    @csrf
                    <div>
                        <label class="block text-sm font-medium">Alasan koreksi</label>
                        <textarea name="reason" required minlength="10" rows="3" class="mt-1 w-full rounded-md border-gray-300">{{ old('reason') }}</textarea>
                    </div>
                    <div>
                        <label class="block text-sm font-medium">Materi</label>
                        <textarea name="materi" required rows="3" class="mt-1 w-full rounded-md border-gray-300">{{ old('materi', $session->attendances->first()?->materi) }}</textarea>
                    </div>
                    <div class="grid gap-4 sm:grid-cols-2">
                        <div><label class="block text-sm font-medium">Kondisi kelas</label><textarea name="classroom_condition" rows="3" class="mt-1 w-full rounded-md border-gray-300">{{ old('classroom_condition', $session->classroom_condition) }}</textarea></div>
                        <div><label class="block text-sm font-medium">Catatan guru</label><textarea name="teacher_notes" rows="3" class="mt-1 w-full rounded-md border-gray-300">{{ old('teacher_notes', $session->teacher_notes) }}</textarea></div>
                    </div>
                    <div class="overflow-x-auto rounded-lg border">
                        <table class="min-w-full text-sm">
                            <thead class="bg-gray-100"><tr><th class="px-3 py-2 text-left">Siswa</th><th class="px-3 py-2 text-left">Status usulan</th></tr></thead>
                            <tbody class="divide-y">
                                @foreach($session->attendances as $attendance)
                                    <tr>
                                        <td class="px-3 py-2">{{ $attendance->student->nama_lengkap }}</td>
                                        <td class="px-3 py-2">
                                            <select name="attendance[{{ $attendance->student_id }}]" class="rounded-md border-gray-300 text-sm">
                                                @foreach(['hadir', 'sakit', 'izin', 'alpa'] as $status)
                                                    <option value="{{ $status }}" @selected(old("attendance.{$attendance->student_id}", $attendance->status) === $status)>{{ ucfirst(str_replace('_', ' ', $status)) }}</option>
                                                @endforeach
                                            </select>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <button class="rounded-md bg-[var(--sabira-primary)] px-4 py-2 text-white hover:bg-[var(--sabira-primary)]">Kirim Permintaan Koreksi</button>
                </form>
            </details>
        @endif

        @if($session->corrections->isNotEmpty())
            <div class="rounded-xl border bg-white p-5">
                <h3 class="font-semibold">Riwayat Koreksi</h3>
                <div class="mt-3 space-y-3 text-sm">
                    @foreach($session->corrections->sortByDesc('created_at') as $correction)
                        <div class="border-l-4 {{ $correction->status === 'approved' ? 'border-green-500' : ($correction->status === 'rejected' ? 'border-red-500' : 'border-amber-500') }} pl-3">
                            <p><strong>{{ ucfirst($correction->status) }}</strong> · {{ $correction->created_at->format('d M Y H:i') }}</p>
                            <p class="text-gray-600">{{ $correction->reason }}</p>
                            @if($correction->review_notes)<p class="text-gray-600">Catatan admin: {{ $correction->review_notes }}</p>@endif
                        </div>
                    @endforeach
                </div>
            </div>
        @endif
    </div>
</x-app-shell>
