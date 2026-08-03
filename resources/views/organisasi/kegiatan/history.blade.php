<x-app-shell>
    <div class="py-6 max-w-4xl mx-auto sm:px-6 lg:px-8">
        <h2 class="text-xl font-bold text-center text-[var(--sabira-ink)] mb-4">
            History Absensi Kegiatan
        </h2>

        <div class="bg-[var(--sabira-surface-soft)] p-6 rounded-xl shadow-md border border-[var(--sabira-border)]">
            <div class="flex justify-between items-center mb-4">
                <div>
                    <div class="text-sm text-[var(--sabira-body)]">
                        <span class="font-semibold">Kegiatan:</span> {{ $kegiatan->kegiatanAsrama->nama }}
                    </div>
                    <div class="text-sm text-[var(--sabira-body)]">
                        <span class="font-semibold">Tanggal:</span> {{ $kegiatan->tanggal }}
                    </div>
                </div>
                <a href="{{ route('asrama.kegiatan') }}"
                    class="inline-flex items-center gap-2 px-3 py-2 bg-[var(--sabira-primary)] hover:bg-[var(--sabira-primary)] text-white rounded-md text-sm shadow transition">
                    <i class="bi bi-arrow-left-circle"></i> Kembali
                </a>
            </div>

            <table id="absensiTable" class="min-w-full divide-y divide-[#D6D8D2] text-sm">
                <thead class="bg-[var(--sabira-surface-strong)] text-[var(--sabira-ink)] uppercase font-semibold">
                    <tr>
                        <th class="px-4 py-3 text-left">NIS</th>
                        <th class="px-4 py-3 text-left">Nama Siswa</th>
                        <th class="px-4 py-3 text-left">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#E7EAE0] text-[var(--sabira-ink)] bg-white">
                    @foreach(\App\Models\Student::orderBy('nama_lengkap')->get() as $siswa)
                        @php
                            $absenSiswa = $absensi->firstWhere('student_id', $siswa->id);
                            $status = $absenSiswa->status ?? 'alpa';
                        @endphp
                        <tr>
                            <td class="px-4 py-3">{{ $siswa->nis }}</td>
                            <td class="px-4 py-3">{{ $siswa->nama_lengkap }}</td>
                            <td class="px-4 py-3 capitalize font-semibold {{ $status === 'hadir' ? 'text-green-600' : 'text-red-600' }}">
                                {{ $status }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

</x-app-shell>
