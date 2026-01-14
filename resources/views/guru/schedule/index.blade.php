<x-user-layout>
    <div class="py-4 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex flex-wrap items-center justify-between gap-4 mb-6">
            <h2 class="font-semibold text-2xl text-[#292D22]">
                Jadwal Mengajar
            </h2>
            <div class="flex flex-wrap items-center gap-2">
                <a href="{{ route('guru.schedule.create', ['guru_id' => $guru->id]) }}"
                   class="inline-flex items-center gap-2 bg-[#8E412E] hover:bg-[#BA6F4D] text-white font-medium px-4 py-2 rounded shadow">
                    <i class="bi bi-plus-circle-fill"></i> Tambah Jadwal
                </a>
                <a href="{{ route('dashboard') }}"
                   class="bg-[#5C644C] text-white px-4 py-2 rounded-md text-sm sm:text-base hover:bg-[#535A44] transition">
                    Kembali
                </a>
            </div>
        </div>

        <div class="bg-[#EEF3E9] shadow-md rounded-2xl p-6 space-y-6">
            <div class="overflow-auto">
                <table class="min-w-[1200px] w-full text-xs text-left text-[#373C2E]">
                    <thead class="bg-[#8D9382] text-white uppercase text-xs font-semibold">
                        <tr>
                            <th class="px-4 py-3 whitespace-nowrap text-center">Jam</th>
                            <th class="px-4 py-3 whitespace-nowrap">WIB</th>
                            @foreach($days as $day)
                                <th class="px-4 py-3 text-center">{{ $day }}</th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#D6D8D2]">
                        @forelse($slotRanges as $slot)
                            <tr class="align-top">
                                <td class="px-4 py-3 text-center font-semibold text-[#1C1E17]">
                                    {{ $slot['index'] }}
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap text-sm font-semibold text-[#1C1E17]">
                                    {{ $slot['start'] }} - {{ $slot['end'] }}
                                </td>
                                @foreach($days as $day)
                                    @php
                                        $cellSchedules = collect(data_get($slotBuckets, $day . '.' . $slot['index'], []));
                                    @endphp
                                    <td class="px-3 py-3 align-top border-l border-[#D6D8D2] {{ $day === 'Jumat' && $slot['index'] > 5 ? 'bg-[#E7EBE1]' : '' }}">
                                        @if($day === 'Jumat' && $slot['index'] > 5)
                                            &nbsp;
                                        @elseif($cellSchedules->isEmpty())
                                            <span class="text-xs text-gray-400">-</span>
                                        @else
                                            <div class="space-y-2">
                                                @foreach($cellSchedules as $schedule)
                                                    <div class="rounded-lg border border-slate-200 bg-white px-3 py-2 shadow-sm">
                                                        <div class="text-[11px] font-semibold text-[#1C1E17]">
                                                            {{ $schedule->subject->nama_mapel }}
                                                        </div>
                                                        <div class="text-[10px] text-slate-600">
                                                            {{ $schedule->classGroup->nama_kelas }} ({{ ucfirst($schedule->classGroup->jenis_kelas) }})
                                                        </div>
                                                        <div class="text-[10px] text-slate-600">
                                                            {{ substr($schedule->jam_mulai, 0, 5) }} - {{ substr($schedule->jam_selesai, 0, 5) }}
                                                        </div>
                                                        <div class="mt-2 flex flex-wrap gap-1">
                                                            <a href="{{ route('guru.schedule.absen', ['schedule' => $schedule->id]) }}"
                                                                class="inline-flex items-center gap-1 px-2 py-1 bg-[#5C644C] text-white text-[10px] rounded hover:bg-[#535A44]">
                                                                <i class="bi bi-clipboard-check"></i> Absen
                                                            </a>
                                                            <a href="{{ route('guru.schedule.edit', ['schedule' => $schedule->id]) }}"
                                                                class="inline-flex items-center gap-1 px-2 py-1 bg-yellow-600 text-white text-[10px] rounded hover:bg-yellow-700">
                                                                <i class="bi bi-pencil-fill"></i> Edit
                                                            </a>
                                                            <form action="{{ route('guru.schedule.destroy', ['schedule' => $schedule->id]) }}"
                                                                method="POST" class="inline delete-form">
                                                                @csrf
                                                                @method('DELETE')
                                                                <button type="submit"
                                                                    class="inline-flex items-center gap-1 px-2 py-1 bg-red-600 text-white text-[10px] rounded hover:bg-red-700">
                                                                    <i class="bi bi-trash-fill"></i> Hapus
                                                                </button>
                                                            </form>
                                                        </div>
                                                    </div>
                                                @endforeach
                                            </div>
                                        @endif
                                    </td>
                                @endforeach
                            </tr>

                            @if($slot['index'] === 4)
                                <tr class="bg-[#D6D8D2]">
                                    <td colspan="{{ count($days) + 2 }}" class="px-4 py-2 text-center text-xs font-semibold text-[#1C1E17] uppercase">
                                        Istirahat 09:55 - 10:25
                                    </td>
                                </tr>
                            @endif
                        @empty
                            <tr>
                                <td colspan="{{ count($days) + 2 }}" class="px-4 py-6 text-center text-[#8D9382]">
                                    Belum ada jadwal mengajar.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if(!empty($outsideSchedules))
                <div class="bg-[#FFF7ED] border border-[#FDBA74] rounded-2xl p-6 shadow-sm">
                    <h3 class="text-sm font-semibold text-[#9A3412] mb-3">Jadwal di Luar Jam Pelajaran</h3>
                    <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
                        @foreach($outsideSchedules as $day => $items)
                            <div class="rounded-xl border border-[#FED7AA] bg-white p-4">
                                <div class="text-sm font-semibold text-[#9A3412] mb-2">{{ $day }}</div>
                                <div class="space-y-2">
                                    @foreach($items as $schedule)
                                        <div class="rounded-lg border border-slate-200 bg-white px-3 py-2 text-[11px] text-slate-600">
                                            <div class="font-semibold text-[#1C1E17]">{{ $schedule->subject->nama_mapel }}</div>
                                            <div>{{ $schedule->classGroup->nama_kelas }} ({{ ucfirst($schedule->classGroup->jenis_kelas) }})</div>
                                            <div>{{ substr($schedule->jam_mulai, 0, 5) }} - {{ substr($schedule->jam_selesai, 0, 5) }}</div>
                                            <div class="mt-2 flex flex-wrap gap-1">
                                                <a href="{{ route('guru.schedule.absen', ['schedule' => $schedule->id]) }}"
                                                    class="inline-flex items-center gap-1 px-2 py-1 bg-[#5C644C] text-white text-[10px] rounded hover:bg-[#535A44]">
                                                    <i class="bi bi-clipboard-check"></i> Absen
                                                </a>
                                                <a href="{{ route('guru.schedule.edit', ['schedule' => $schedule->id]) }}"
                                                    class="inline-flex items-center gap-1 px-2 py-1 bg-yellow-600 text-white text-[10px] rounded hover:bg-yellow-700">
                                                    <i class="bi bi-pencil-fill"></i> Edit
                                                </a>
                                                <form action="{{ route('guru.schedule.destroy', ['schedule' => $schedule->id]) }}"
                                                    method="POST" class="inline delete-form">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit"
                                                        class="inline-flex items-center gap-1 px-2 py-1 bg-red-600 text-white text-[10px] rounded hover:bg-red-700">
                                                        <i class="bi bi-trash-fill"></i> Hapus
                                                    </button>
                                                </form>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>
    </div>
</x-user-layout>
