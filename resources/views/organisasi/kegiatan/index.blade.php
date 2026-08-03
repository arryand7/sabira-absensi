<x-app-shell>
    <div class="text-center mt-4">
        <h2 class="text-2xl font-semibold text-[var(--sabira-ink)]">Daftar Kegiatan Asrama</h2>
    </div>

    <div class="py-6 max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
        {{-- Tombol tambah kegiatan --}}

        <div class="flex items-center justify-between mb-2">
            <button onclick="document.getElementById('formKegiatan').classList.toggle('hidden')"
                class="inline-flex items-center px-4 py-2 bg-[var(--sabira-primary)] text-white text-sm font-medium rounded-lg shadow hover:bg-[var(--sabira-primary)] transition">
                <i class="bi bi-plus-lg mr-2"></i> Tambah Kegiatan
            </button>
            <a href="{{ route('asrama.index') }}"
            class="inline-flex items-center gap-2 px-4 py-2 bg-[var(--sabira-primary)] hover:bg-[var(--sabira-primary-active)] text-white rounded-md text-sm shadow transition">
                <i class="bi bi-arrow-left-circle"></i> Kembali
            </a>
        </div>

        {{-- Form input kegiatan --}}
        <div id="formKegiatan" class="hidden mb-6 bg-[var(--sabira-surface-soft)] rounded-xl shadow p-6 space-y-4 border border-[var(--sabira-border)]">
            <form action="{{ route('asrama.kegiatan.create') }}" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label for="nama" class="block text-sm font-medium text-[var(--sabira-body)]">Nama Kegiatan</label>
                    <input type="text" name="nama" id="nama"
                        class="mt-1 w-full rounded-lg border-[var(--sabira-border)] shadow-sm focus:ring-[#C6D2B2] focus:border-[var(--sabira-border)]" required>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label for="tanggal" class="block text-sm font-medium text-[var(--sabira-body)]">Tanggal</label>
                        <input type="date" name="tanggal" id="tanggal"
                            class="mt-1 w-full rounded-lg border-[var(--sabira-border)] shadow-sm focus:ring-[#C6D2B2] focus:border-[var(--sabira-border)]" required>
                    </div>

                    <div>
                        <label for="jam_mulai" class="block text-sm font-medium text-[var(--sabira-body)]">Jam Mulai</label>
                        <input type="time" name="jam_mulai" id="jam_mulai"
                            class="mt-1 w-full rounded-lg border-[var(--sabira-border)] shadow-sm focus:ring-[#C6D2B2] focus:border-[var(--sabira-border)]" required>
                    </div>

                    <div>
                        <label for="jam_selesai" class="block text-sm font-medium text-[var(--sabira-body)]">Jam Selesai</label>
                        <input type="time" name="jam_selesai" id="jam_selesai"
                            class="mt-1 w-full rounded-lg border-[var(--sabira-border)] shadow-sm focus:ring-[#C6D2B2] focus:border-[var(--sabira-border)]" required>
                    </div>
                </div>

                <div class="text-right">
                    <button type="submit"
                        class="inline-flex items-center px-4 py-2 bg-[var(--sabira-primary)] text-white text-sm font-medium rounded-lg shadow hover:bg-[var(--sabira-primary)] transition">
                        <i class="bi bi-check-lg mr-2"></i> Simpan
                    </button>
                </div>
            </form>
        </div>

        {{-- Tabel kegiatan --}}
        <div class="overflow-x-auto bg-white rounded-2xl shadow-md border border-[var(--sabira-border)] px-6 py-4">
            <table id="kegiatanTable" class="min-w-full divide-y divide-gray-200 text-sm text-left text-[var(--sabira-ink)]">
                <thead class="bg-[var(--sabira-surface-strong)] text-[var(--sabira-ink)] uppercase font-semibold tracking-wide">
                    <tr>
                        <th class="px-6 py-4">Nama Kegiatan</th>
                        <th class="px-6 py-4">Tanggal</th>
                        <th class="px-6 py-4">Jam</th>
                        <th class="px-6 py-4 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#F1F1EF] bg-white">
                    @foreach($kegiatan->sortByDesc('tanggal') as $k)
                        <tr class="hover:bg-[var(--sabira-surface-soft)] transition-colors duration-150">
                            <td class="px-6 py-4 whitespace-nowrap">{{ $k->kegiatanAsrama->nama }}</td>
                            <td class="px-6 py-4 whitespace-nowrap">{{ $k->tanggal }}</td>
                            <td class="px-6 py-4 whitespace-nowrap">{{ $k->jam_mulai }} - {{ $k->jam_selesai }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-center space-x-2">
                                @if($k->sudah_dinilai)
                                    <span class="inline-flex items-center bg-gray-400 text-white px-3 py-1.5 rounded-lg text-xs font-medium shadow">
                                        <i class="bi bi-clipboard-check mr-1"></i> Sudah Absen
                                    </span>
                                @else
                                    <a href="{{ route('asrama.kegiatan.absen', $k->id) }}"
                                        class="inline-flex items-center bg-[var(--sabira-primary)] hover:bg-[var(--sabira-primary)] text-white px-3 py-1.5 rounded-lg text-xs font-medium shadow transition">
                                        <i class="bi bi-clipboard-check mr-1"></i> Absen
                                    </a>
                                @endif
                                <a href="{{ route('asrama.kegiatan.history', $k->id) }}"
                                    class="inline-flex items-center bg-[#8B8E7C] hover:bg-[#757867] text-white px-3 py-1.5 rounded-lg text-xs font-medium shadow transition">
                                    <i class="bi bi-clock-history mr-1"></i> History
                                </a>
                                <form action="{{ route('asrama.kegiatan.delete', $k->id) }}" method="POST" class="inline-block"
                                    onsubmit="return confirm('Yakin ingin menghapus kegiatan ini?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit"
                                        class="inline-flex items-center bg-red-600 hover:bg-red-700 text-white px-3 py-1.5 rounded-lg text-xs font-medium shadow transition">
                                        <i class="bi bi-trash-fill mr-1"></i> Hapus
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

    </div>

</x-app-shell>
