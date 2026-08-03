<x-app-shell>
<div class="sm:px-6 lg:px-8"><x-page-title title="IZIN DAN KOREKSI" /></div>

    <div class="mt-6 space-y-5 sm:px-6 lg:px-8">
        <div class="grid grid-cols-3 gap-3">
            @foreach(['pending' => 'Menunggu', 'approved' => 'Disetujui', 'rejected' => 'Ditolak'] as $key => $label)
                <a href="{{ route('admin.attendance-corrections.index', ['status' => $key]) }}" class="rounded-xl border bg-white p-4 shadow-sm {{ $status === $key ? 'ring-2 ring-[#8E412E]' : '' }}">
                    <span class="block text-xs uppercase text-gray-500">{{ $label }}</span><strong class="text-2xl">{{ $counts[$key] ?? 0 }}</strong>
                </a>
            @endforeach
        </div>

        <div class="flex flex-wrap gap-2">
            <a href="{{ route('admin.attendance-corrections.index') }}" class="rounded-md px-3 py-2 text-sm {{ $status === '' ? 'bg-[var(--sabira-primary)] text-white' : 'bg-gray-200' }}">Semua</a>
            @foreach(['pending' => 'Menunggu', 'approved' => 'Disetujui', 'rejected' => 'Ditolak'] as $key => $label)
                <a href="{{ route('admin.attendance-corrections.index', ['status' => $key]) }}" class="rounded-md px-3 py-2 text-sm {{ $status === $key ? 'bg-[var(--sabira-primary)] text-white' : 'bg-gray-200' }}">{{ $label }}</a>
            @endforeach
        </div>

        <div class="overflow-x-auto rounded-xl bg-white shadow-sm">
            <table class="min-w-full text-sm">
                <thead class="bg-[var(--sabira-primary)] text-white"><tr><th class="px-4 py-3 text-left">Diajukan</th><th class="px-4 py-3 text-left">Guru</th><th class="px-4 py-3 text-left">Sesi</th><th class="px-4 py-3 text-left">Status</th><th class="px-4 py-3 text-right">Aksi</th></tr></thead>
                <tbody class="divide-y">
                    @forelse($corrections as $correction)
                        <tr>
                            <td class="px-4 py-3">{{ $correction->created_at->format('d M Y H:i') }}</td>
                            <td class="px-4 py-3">{{ $correction->requester->name }}</td>
                            <td class="px-4 py-3">{{ $correction->session->schedule->subject->nama_mapel }} · {{ $correction->session->schedule->classGroup->nama_kelas }} · P{{ $correction->session->meeting_no }}</td>
                            <td class="px-4 py-3 capitalize">{{ $correction->status }}</td>
                            <td class="px-4 py-3 text-right"><a href="{{ route('admin.attendance-corrections.show', $correction) }}" class="text-[#8E412E] hover:underline">Tinjau</a></td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="px-4 py-10 text-center text-gray-500">Belum ada permintaan koreksi.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        {{ $corrections->links() }}
    </div>
</x-app-shell>
