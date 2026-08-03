<x-app-shell>
<div class="p-4">
        <main class="mt-6 space-y-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                {{-- REGULER --}}
                <div class="bg-[var(--sabira-surface)] p-6 shadow-md rounded-2xl">
                    <h3 class="text-lg font-semibold mb-4 text-[var(--sabira-body)]">Reguler</h3>
                    <form method="GET" action="{{ route('laporan.murid.mapel') }}" class="space-y-4">
                        <input type="hidden" name="jenis" value="formal">

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Kelas</label>
                            <select name="kelas" class="w-full rounded-md border-gray-300 shadow-sm">
                                <option value="">Pilih Kelas</option>
                                @foreach($kelasFormal as $kelas)
                                    <option value="{{ $kelas }}" {{ request('kelas') == $kelas && request('jenis') == 'formal' ? 'selected' : '' }}>
                                        {{ $kelas }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Mata Pelajaran</label>
                            <select name="mapel" class="w-full rounded-md border-gray-300 shadow-sm">
                                <option value="">Pilih Mapel</option>
                                @foreach($mapelFormal as $mapel)
                                    <option value="{{ $mapel }}" {{ request('mapel') == $mapel && request('jenis') == 'formal' ? 'selected' : '' }}>
                                        {{ $mapel }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Tahun Ajaran</label>
                            <select name="tahun_ajaran" class="w-full rounded-md border-gray-300 shadow-sm">
                                <option value="">Pilih Tahun Ajaran</option>
                                @foreach($academicYears as $tahun)
                                    <option value="{{ $tahun->id }}"
                                        {{ (request('tahun_ajaran') ?? $tahunAktif?->id) == $tahun->id ? 'selected' : '' }}>
                                        {{ $tahun->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>


                        <div class="flex gap-2">
                            <button type="submit" class="bg-[var(--sabira-primary)] text-white px-4 py-2 rounded shadow hover:bg-[var(--sabira-primary-active)]">
                                <i class="bi bi-eye-fill"></i> Preview
                            </button>

                            @if(request('jenis') == 'formal' && request('kelas') && request('mapel'))
                               <a href="{{ route('laporan.murid.mapel.download', request()->only('jenis', 'kelas', 'mapel', 'tahun_ajaran')) }}"
                                   class="bg-[var(--sabira-primary)] text-white px-4 py-2 rounded shadow hover:bg-[var(--sabira-primary-active)]">
                                    <i class="bi bi-file-earmark-pdf-fill"></i> Download PDF
                                </a>
                                <a href="{{ route('laporan.murid.mapel.excel', request()->only('jenis', 'kelas', 'mapel', 'tahun_ajaran')) }}"
                                class="bg-[var(--sabira-primary)] text-white px-4 py-2 rounded shadow hover:bg-[var(--sabira-primary-active)]">
                                    <i class="bi bi-file-earmark-excel-fill"></i> Download Excel
                                </a>
                            @endif
                        </div>
                    </form>
                </div>

                {{-- NON REGULER --}}
                <div class="bg-[var(--sabira-surface)] p-6 shadow-md rounded-2xl">
                    <h3 class="text-lg font-semibold mb-4 text-[var(--sabira-body)]">Non Reguler</h3>
                    <form method="GET" action="{{ route('laporan.murid.mapel') }}" class="space-y-4">
                        <input type="hidden" name="jenis" value="muadalah">

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Kelas</label>
                            <select name="kelas" class="w-full rounded-md border-gray-300 shadow-sm">
                                <option value="">Pilih Kelas</option>
                                @foreach($kelasMuadalah as $kelas)
                                    <option value="{{ $kelas }}" {{ request('kelas') == $kelas && request('jenis') == 'muadalah' ? 'selected' : '' }}>
                                        {{ $kelas }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Mata Pelajaran</label>
                            <select name="mapel" class="w-full rounded-md border-gray-300 shadow-sm">
                                <option value="">Pilih Mapel</option>
                                @foreach($mapelMuadalah as $mapel)
                                    <option value="{{ $mapel }}" {{ request('mapel') == $mapel && request('jenis') == 'muadalah' ? 'selected' : '' }}>
                                        {{ $mapel }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Tahun Ajaran</label>
                            <select name="tahun_ajaran" class="w-full rounded-md border-gray-300 shadow-sm">
                                <option value="">Pilih Tahun Ajaran</option>
                                @foreach($academicYears as $tahun)
                                    <option value="{{ $tahun->id }}"
                                        {{ (request('tahun_ajaran') ?? $tahunAktif?->id) == $tahun->id ? 'selected' : '' }}>
                                        {{ $tahun->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="flex gap-2">
                            <button type="submit" class="bg-[var(--sabira-primary)] text-white px-4 py-2 rounded shadow hover:bg-[var(--sabira-primary-active)]">
                                <i class="bi bi-eye-fill"></i> Preview
                            </button>

                            @if(request('jenis') == 'muadalah' && request('kelas') && request('mapel'))
                                <a href="{{ route('laporan.murid.mapel.download', request()->only('jenis', 'kelas', 'mapel', 'tahun_ajaran')) }}"
                                   class="bg-[var(--sabira-primary)] text-white px-4 py-2 rounded shadow hover:bg-[var(--sabira-primary-active)]">
                                    <i class="bi bi-file-earmark-pdf-fill"></i> Download PDF
                                </a>
                                <a href="{{ route('laporan.murid.mapel.excel', request()->only('jenis', 'kelas', 'mapel', 'tahun_ajaran')) }}"
                                class="bg-[var(--sabira-primary)] text-white px-4 py-2 rounded shadow hover:bg-[var(--sabira-primary-active)]">
                                    <i class="bi bi-file-earmark-excel-fill"></i> Download Excel
                                </a>
                            @endif
                        </div>
                    </form>
                </div>
            </div>

            {{-- TABEL PREVIEW --}}
            @if($rekapMapel)
                <div class="bg-[var(--sabira-surface)] p-6 rounded-2xl shadow-md">
                    <h4 class="text-lg font-semibold mb-4 text-[var(--sabira-body)]">Preview Rekap Kehadiran</h4>
                    <div class="overflow-x-auto max-h-[500px] overflow-y-auto rounded border border-gray-300">
                        <table id="rekapTable" class="w-full text-sm text-[var(--sabira-body)]">
                            <thead class="bg-[var(--sabira-neutral-strong)] text-white text-xs uppercase font-semibold">
                                <tr>
                                    <th class="px-4 py-3">Nama</th>
                                    <th class="px-4 py-3">NIS</th>
                                    <th class="px-4 py-3">Kelas</th>
                                    <th class="px-4 py-3 text-center">H</th>
                                    <th class="px-4 py-3 text-center">I</th>
                                    <th class="px-4 py-3 text-center">S</th>
                                    <th class="px-4 py-3 text-center">A</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-[#D6D8D2]">
                                @foreach($rekapMapel as $row)
                                    <tr class="hover:bg-[var(--sabira-surface-strong)] transition">
                                        <td class="px-4 py-2">{{ $row['nama'] }}</td>
                                        <td class="px-4 py-2">{{ $row['nis'] }}</td>
                                        <td class="px-4 py-2">{{ $row['kelas'] }}</td>
                                        <td class="px-4 py-2 text-center">{{ $row['H'] }}</td>
                                        <td class="px-4 py-2 text-center">{{ $row['I'] }}</td>
                                        <td class="px-4 py-2 text-center">{{ $row['S'] }}</td>
                                        <td class="px-4 py-2 text-center">{{ $row['A'] }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

            @endif
        </main>
    </div>
</x-app-shell>
