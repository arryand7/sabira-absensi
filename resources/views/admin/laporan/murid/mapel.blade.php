<x-app-layout>
    <x-slot name="sidebar">
        <x-admin-sidenav />
    </x-slot>

    <div class="p-4">
        <main class="mt-6 space-y-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                {{-- AKADEMIK --}}
                <div class="bg-[#EEF3E9] p-6 shadow-md rounded-2xl">
                    <h3 class="text-lg font-semibold mb-4 text-[#374151]">Akademik</h3>
                    <form method="GET" action="{{ route('laporan.murid.mapel') }}" class="space-y-4">
                        <input type="hidden" name="jenis" value="akademik">

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Kelas</label>
                            <select name="kelas" class="w-full rounded-md border-gray-300 shadow-sm">
                                <option value="">Pilih Kelas</option>
                                @foreach($kelasAkademik as $kelas)
                                    <option value="{{ $kelas }}" {{ request('kelas') == $kelas && request('jenis') == 'akademik' ? 'selected' : '' }}>
                                        {{ $kelas }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Mata Pelajaran</label>
                            <select name="mapel" class="w-full rounded-md border-gray-300 shadow-sm">
                                <option value="">Pilih Mapel</option>
                                @foreach($mapelAkademik as $mapel)
                                    <option value="{{ $mapel }}" {{ request('mapel') == $mapel && request('jenis') == 'akademik' ? 'selected' : '' }}>
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
                            <button type="submit" class="bg-[#8E412E] text-white px-4 py-2 rounded shadow hover:bg-[#BA6F4D]">
                                <i class="bi bi-eye-fill"></i> Preview
                            </button>

                            @if(request('jenis') == 'akademik' && request('kelas') && request('mapel'))
                               <a href="{{ route('laporan.murid.mapel.download', request()->only('jenis', 'kelas', 'mapel', 'tahun_ajaran')) }}"
                                   class="bg-green-600 text-white px-4 py-2 rounded shadow hover:bg-green-700">
                                    <i class="bi bi-file-earmark-pdf-fill"></i> Download PDF
                                </a>
                            @endif
                        </div>
                    </form>
                </div>

                {{-- MUADALAH --}}
                <div class="bg-[#EEF3E9] p-6 shadow-md rounded-2xl">
                    <h3 class="text-lg font-semibold mb-4 text-[#374151]">Muadalah</h3>
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
                            <button type="submit" class="bg-[#8E412E] text-white px-4 py-2 rounded shadow hover:bg-[#BA6F4D]">
                                <i class="bi bi-eye-fill"></i> Preview
                            </button>

                            @if(request('jenis') == 'muadalah' && request('kelas') && request('mapel'))
                                <a href="{{ route('laporan.murid.mapel.download', request()->only('jenis', 'kelas', 'mapel', 'tahun_ajaran')) }}"
                                   class="bg-green-600 text-white px-4 py-2 rounded shadow hover:bg-green-700">
                                    <i class="bi bi-file-earmark-pdf-fill"></i> Download PDF
                                </a>
                            @endif
                        </div>
                    </form>
                </div>
            </div>

            {{-- TABEL PREVIEW --}}
            @if($rekapMapel)
                <div class="bg-[#EEF3E9] p-6 rounded-2xl shadow-md">
                    <h4 class="text-lg font-semibold mb-4 text-[#374151]">Preview Rekap Kehadiran</h4>
                    <div class="overflow-x-auto max-h-[500px] overflow-y-auto rounded border border-gray-300">
                        <table id="rekapTable" class="w-full text-sm text-[#373C2E]">
                            <thead class="bg-[#8D9382] text-white text-xs uppercase font-semibold">
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
                                    <tr class="hover:bg-[#BEC1B7] transition">
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

                @push('scripts')
                    <script>
                        $(document).ready(function () {
                            $('#rekapTable').DataTable({
                                pageLength: 25,
                                language: {
                                    search: "Cari:",
                                    lengthMenu: "Tampilkan _MENU_ entri",
                                    info: "Menampilkan _START_ sampai _END_ dari _TOTAL_ entri",
                                    paginate: {
                                        first: "Awal",
                                        last: "Akhir",
                                        next: "›",
                                        previous: "‹"
                                    },
                                    zeroRecords: "Tidak ditemukan data yang cocok",
                                }
                            });
                        });
                    </script>
                @endpush
            @endif
        </main>
    </div>
</x-app-layout>
