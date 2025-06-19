<div class="overflow-x-auto">
    <table class="min-w-full border border-[#C6C9BD] rounded-lg shadow-sm text-sm text-center bg-white">
        <thead>
            <tr class="bg-[#DDE3D3] text-[#292D22] uppercase">
                <th rowspan="2" class="border border-[#C6C9BD] p-2 font-semibold">No</th>
                <th rowspan="2" class="border border-[#C6C9BD] p-2 font-semibold">Nama</th>
                <th rowspan="2" class="border border-[#C6C9BD] p-2 font-semibold">Waktu</th>
                <th colspan="{{ $tanggal->count() }}" class="border border-[#C6C9BD] p-2 font-semibold">
                    Tanggal ({{ \Carbon\Carbon::create()->month($bulan)->translatedFormat('F') }})
                </th>
            </tr>
            <tr class="bg-[#F0F3EB] text-[#3E4434] text-xs">
                @foreach($tanggal as $tgl)
                    <th class="border border-[#C6C9BD] p-1 font-medium">{{ $tgl }}</th>
                @endforeach
            </tr>
        </thead>
        <tbody class="text-[#292D22]">
            @php $no = 1; @endphp
            @foreach($students as $student)
                @foreach($sholatList as $index => $sholat)
                    <tr class="{{ $loop->parent->even ? 'bg-[#F7F9F4]' : 'bg-white' }}">
                        @if ($loop->first)
                            <td class="border border-[#D6D8D2] p-2 align-top" rowspan="{{ $sholatList->count() }}">
                                {{ $no++ }}
                            </td>
                            <td class="border border-[#D6D8D2] p-2 text-left align-top" rowspan="{{ $sholatList->count() }}">
                                <span class="font-medium">{{ $student->nama_lengkap }}</span>
                            </td>
                        @endif
                        <td class="border border-[#D6D8D2] p-2">{{ $sholat->nama }}</td>
                        @foreach($tanggal as $tgl)
                            @php
                                $status = $data[$student->id][$sholat->id][$tgl] ?? null;
                                $bgColor = match($status) {
                                    'hadir' => 'bg-green-100 text-green-700 font-semibold',
                                    'alpa' => 'bg-red-100 text-red-700 font-semibold',
                                    default => 'bg-gray-100 text-gray-400',
                                };
                                $symbol = match($status) {
                                    'hadir' => '✓',
                                    'alpa' => '×',
                                    default => '-',
                                };
                            @endphp
                            <td class="border border-[#D6D8D2] p-1 {{ $bgColor }}">
                                {{ $symbol }}
                            </td>
                        @endforeach
                    </tr>
                @endforeach
            @endforeach
        </tbody>
    </table>
</div>
