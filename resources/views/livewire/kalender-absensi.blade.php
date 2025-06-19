<div class="grid grid-cols-2 sm:grid-cols-4 md:grid-cols-7 gap-2 text-blue-900">
    @foreach ($absensiMap as $tanggal => $status)
        @php
            $tanggalObj = \Carbon\Carbon::parse($tanggal);
        @endphp
        <div class="
            flex flex-col justify-center items-center
            p-3 min-h-[70px] rounded-lg text-center text-sm shadow-sm
            @if ($status === 'Hadir') bg-green-100 text-green-800 border border-green-300
            @elseif ($status === 'Terlambat') bg-yellow-100 text-yellow-800 border border-yellow-300
            @elseif ($status === 'Tidak Hadir') bg-red-100 text-red-800 border border-red-300
            @else bg-gray-50 text-gray-400 border border-gray-200
            @endif
        ">
            <div class="font-bold text-lg">{{ $tanggalObj->format('d') }}</div>
            <div class="text-xs leading-tight mt-1">
                {{ $status !== '-' ? $status : '' }}
            </div>
        </div>
    @endforeach
</div>
