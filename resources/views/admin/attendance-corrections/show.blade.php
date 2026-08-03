<x-app-shell>
<div class="sm:px-6 lg:px-8"><x-page-title title="TINJAU KOREKSI" /></div>

    <div class="mt-6 space-y-5 sm:px-6 lg:px-8">
        @if(session('success'))<div class="rounded-lg border border-green-200 bg-green-50 p-3 text-green-800">{{ session('success') }}</div>@endif
        @if($errors->any())<div class="rounded-lg border border-red-200 bg-red-50 p-3 text-red-800">{{ $errors->first() }}</div>@endif

        <div class="rounded-xl border bg-white p-5 shadow-sm">
            <div class="flex flex-wrap justify-between gap-3">
                <div><h2 class="font-semibold">{{ $correction->session->schedule->subject->nama_mapel }} · {{ $correction->session->schedule->classGroup->nama_kelas }}</h2><p class="text-sm text-gray-600">Pertemuan {{ $correction->session->meeting_no }} · {{ \Carbon\Carbon::parse($correction->session->date)->format('d M Y') }}</p></div>
                <span class="h-fit rounded-full bg-gray-100 px-3 py-1 text-sm capitalize">{{ $correction->status }}</span>
            </div>
            <dl class="mt-4 grid gap-3 text-sm sm:grid-cols-2"><div><dt class="text-gray-500">Pemohon</dt><dd class="font-medium">{{ $correction->requester->name }}</dd></div><div><dt class="text-gray-500">Alasan</dt><dd>{{ $correction->reason }}</dd></div></dl>
        </div>

        <div class="grid gap-5 lg:grid-cols-2">
            @foreach(['before_payload' => 'Data Sebelum', 'proposed_payload' => 'Usulan Perubahan'] as $field => $title)
                @php($payload = $correction->{$field})
                <div class="rounded-xl border bg-white p-5 shadow-sm">
                    <h3 class="font-semibold">{{ $title }}</h3>
                    <dl class="mt-3 space-y-2 text-sm"><div><dt class="text-gray-500">Materi</dt><dd>{{ $payload['materi'] ?? '-' }}</dd></div><div><dt class="text-gray-500">Kondisi kelas</dt><dd>{{ $payload['classroom_condition'] ?? '-' }}</dd></div><div><dt class="text-gray-500">Catatan</dt><dd>{{ $payload['teacher_notes'] ?? '-' }}</dd></div></dl>
                    <div class="mt-4 max-h-80 overflow-auto"><table class="w-full text-sm"><thead><tr class="border-b"><th class="py-2 text-left">Siswa</th><th class="py-2 text-left">Status</th></tr></thead><tbody>
                        @foreach($correction->session->attendances as $attendance)
                            <tr class="border-b"><td class="py-2">{{ $attendance->student->nama_lengkap }}</td><td class="py-2 capitalize">{{ str_replace('_', ' ', $payload['attendance'][$attendance->student_id] ?? '-') }}</td></tr>
                        @endforeach
                    </tbody></table></div>
                </div>
            @endforeach
        </div>

        @if($correction->status === 'pending')
            <form method="POST" action="{{ route('admin.attendance-corrections.review', $correction) }}" class="rounded-xl border bg-white p-5 shadow-sm">
                @csrf
                <label class="block text-sm font-medium">Catatan peninjauan</label>
                <textarea name="review_notes" rows="3" class="mt-1 w-full rounded-md border-gray-300" placeholder="Wajib diisi jika menolak">{{ old('review_notes') }}</textarea>
                <div class="mt-4 flex flex-wrap gap-2">
                    <button name="decision" value="approved" class="rounded-md bg-green-700 px-4 py-2 text-white">Setujui dan Terapkan</button>
                    <button name="decision" value="rejected" class="rounded-md bg-red-700 px-4 py-2 text-white">Tolak</button>
                    <a href="{{ route('admin.attendance-corrections.index') }}" class="rounded-md bg-gray-200 px-4 py-2">Kembali</a>
                </div>
            </form>
        @else
            <div class="rounded-xl border bg-gray-50 p-5 text-sm"><strong>Ditinjau oleh {{ $correction->reviewer?->name ?? '-' }}</strong><p class="mt-1 text-gray-600">{{ $correction->review_notes ?: 'Tanpa catatan.' }}</p></div>
        @endif
    </div>
</x-app-shell>
