<x-app-shell>
<div class="mt-6 w-full sm:px-6 lg:px-8 space-y-6">
        <div class="bg-[var(--sabira-surface)] shadow-md rounded-2xl p-6">
            {{-- Filter --}}
            <form method="GET" id="filterForm" class="mb-6 flex flex-wrap items-end gap-4">
                <div>
                    <label for="tahun_ajaran" class="block text-sm font-medium text-gray-700">Tahun Ajaran</label>
                    <select name="tahun_ajaran" id="tahun_ajaran" class="w-full rounded-md border-gray-300 shadow-sm focus:ring focus:ring-orange-200">
                        @foreach($academicYears as $tahun)
                            <option value="{{ $tahun->id }}" {{ (string) $selectedYear === (string) $tahun->id ? 'selected' : '' }}>
                                {{ $tahun->name }}{{ $tahun->is_active ? ' (Aktif)' : '' }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="kelas" class="block text-sm font-medium text-gray-700">Kelas</label>
                    <select name="kelas" id="kelas" class="w-full rounded-md border-gray-300 shadow-sm focus:ring focus:ring-orange-200">
                        <option value="">Semua</option>
                        @foreach($kelasList as $kelas)
                            <option value="{{ $kelas }}" {{ request('kelas') == $kelas ? 'selected' : '' }}>
                                {{ $kelas }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="flex gap-2 mt-1 flex-wrap">
                    <button type="submit"
                            class="bg-[var(--sabira-primary)] text-white px-4 py-2 rounded-md hover:bg-[var(--sabira-primary-active)] flex items-center gap-2 shadow">
                        <i class="bi bi-funnel-fill"></i> Filter
                    </button>

                    @if(request('kelas'))
                        <a href="{{ route('laporan.murid.kelas.export.pdf', request()->only('kelas', 'tahun_ajaran')) }}"
                           class="bg-[var(--sabira-primary)] text-white px-4 py-2 rounded-md hover:bg-[var(--sabira-primary-active)] flex items-center gap-2 shadow">
                            <i class="bi bi-file-earmark-pdf-fill"></i> Download PDF Kelas
                        </a>
                        <a href="{{ route('laporan.murid.kelas.export.excel', request()->only('kelas', 'tahun_ajaran')) }}"
                           class="bg-[var(--sabira-primary)] text-white px-4 py-2 rounded-md hover:bg-[var(--sabira-primary-active)] flex items-center gap-2 shadow">
                            <i class="bi bi-file-earmark-excel-fill"></i> Download Excel Kelas
                        </a>
                    @endif

                    <a href="{{ route('laporan.murid') }}"
                       class="bg-gray-500 text-white px-4 py-2 rounded-md hover:bg-gray-600 flex items-center gap-2 shadow">
                        <i class="bi bi-x-circle-fill"></i> Reset
                    </a>
                </div>
            </form>

            {{-- Table --}}
            <div class="overflow-x-auto">
                <table id="laporanTable" class="w-full text-sm text-left text-[var(--sabira-body)]">
                    <thead class="bg-[var(--sabira-neutral-strong)] text-white uppercase text-xs font-semibold">
                        <tr>
                            <th class="px-4 py-3">Nama</th>
                            <th class="px-4 py-3">NIS</th>
                            <th class="px-4 py-3">Kelas</th>
                            <th class="px-4 py-3 text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#D6D8D2]">
                        @if ($students->count() > 0)
                            @foreach($students as $student)
                                <tr class="hover:bg-[var(--sabira-surface-strong)] transition">
                                    <td class="px-4 py-3">{{ $student->nama_lengkap }}</td>
                                    <td class="px-4 py-3">{{ $student->nis }}</td>
                                    <td class="px-4 py-3">{{ $student->kelas }}</td>
                                    <td class="px-4 py-3 text-center">
                                        <div class="flex items-center justify-center gap-2">
                                            <a href="{{ route('laporan.murid.show', ['student' => $student->id, 'tahun_ajaran' => request('tahun_ajaran')]) }}"
                                               class="inline-flex items-center gap-1 px-3 py-1 bg-[var(--sabira-primary)] text-white rounded-md text-xs hover:bg-[var(--sabira-primary)] shadow">
                                                <i class="bi bi-eye-fill"></i> Detail
                                            </a>
                                            <a href="{{ route('laporan.murid.download', ['student' => $student->id, 'tahun_ajaran' => request('tahun_ajaran')]) }}"
                                               class="inline-flex items-center gap-1 px-3 py-1 bg-[var(--sabira-primary)] text-white rounded-md text-xs hover:bg-[var(--sabira-primary-active)] shadow">
                                                <i class="bi bi-file-earmark-pdf-fill"></i> PDF
                                            </a>
                                            <a href="{{ route('laporan.murid.download.excel', ['student' => $student->id, 'tahun_ajaran' => request('tahun_ajaran')]) }}"
                                               class="inline-flex items-center gap-1 px-3 py-1 bg-[var(--sabira-primary)] text-white rounded-md text-xs hover:bg-[var(--sabira-primary-active)] shadow">
                                                <i class="bi bi-file-earmark-excel-fill"></i> Excel
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        @else
                            <tr>
                                <td colspan="4" class="text-center py-6 text-slate-500 italic">
                                    Tidak ada data murid.
                                </td>
                            </tr>
                        @endif
                    </tbody>
                </table>
            </div>
            <div class="mt-4">{{ $students->links() }}</div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const form = document.getElementById('filterForm');
            ['kelas', 'tahun_ajaran'].forEach((id) => {
                document.getElementById(id)?.addEventListener('change', () => form?.requestSubmit());
            });
        });
    </script>
</x-app-shell>
