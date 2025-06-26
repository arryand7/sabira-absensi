<div x-data="{ showModal: false, detail: {} }">
    <div class="grid grid-cols-2 sm:grid-cols-4 md:grid-cols-7 gap-2 text-blue-900">
        @foreach ($absensiMap as $tanggal => $data)
            @php
                $tanggalObj = \Carbon\Carbon::parse($tanggal);
            @endphp
            <div
                class="p-3 rounded-lg shadow-sm cursor-pointer text-center text-sm"
                @click="detail = {{ json_encode([
                    'tanggal' => $tanggalObj->translatedFormat('d F Y'),
                    'status' => $data['status'],
                    'check_in' => $data['check_in'],
                    'check_out' => $data['check_out'],
                ]) }}; showModal = true"
                :class="{
                    'bg-green-100 text-green-800 border border-green-300': '{{ $data['status'] }}' === 'Hadir',
                    'bg-yellow-100 text-yellow-800 border border-yellow-300': '{{ $data['status'] }}' === 'Terlambat',
                    'bg-red-100 text-red-800 border border-red-300': '{{ $data['status'] }}' === 'Tidak Hadir',
                    'bg-gray-50 text-gray-400 border border-gray-200': '{{ $data['status'] }}' === '-',
                }"
            >
                <div class="font-bold text-lg">{{ $tanggalObj->format('d') }}</div>
                <div class="text-xs leading-tight mt-1">
                    {{ $data['status'] !== '-' ? $data['status'] : '' }}
                </div>
            </div>
        @endforeach
    </div>

    <!-- Modal -->
    <div
        x-show="showModal"
        style="display: none;"
        class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-40"
    >
        <div class="bg-white p-6 rounded-md shadow-lg max-w-sm w-full">
            <h2 class="text-lg font-semibold mb-2" x-text="detail.tanggal"></h2>
            <p>Status: <span x-text="detail.status"></span></p>
            <p>Check-In: <span x-text="detail.check_in ?? '-'"></span></p>
            <p>Check-Out: <span x-text="detail.check_out ?? '-'"></span></p>
            <button @click="showModal = false" class="mt-4 bg-gray-200 px-4 py-2 rounded hover:bg-gray-300">
                Tutup
            </button>
        </div>
    </div>
</div>
