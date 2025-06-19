<x-app-layout>
    <x-slot name="sidebar">
        <x-admin-sidenav />
    </x-slot>

    <div class="mt-6 w-full sm:px-6 lg:px-8 space-y-6">
        <div class="bg-[#EEF3E9] shadow-md rounded-2xl p-6 max-w-4xl mx-auto">

            <h1 class="text-xl font-semibold text-[#292D22] mb-4">Migrasi Siswa</h1>

            {{-- Flash Message --}}
            @if(session('success'))
                <div class="text-green-600 font-medium mb-4">{{ session('success') }}</div>
            @endif

            <form method="POST" action="{{ route('promotion.promote') }}" class="space-y-4">
                @csrf

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block font-semibold text-sm text-[#373C2E] mb-1">Dari Kelas (Tahun Ajaran Nonaktif)</label>
                        <select name="from_class_id" required
                                class="w-full px-4 py-2 border border-gray-300 rounded-md focus:ring focus:ring-blue-200">
                            <option disabled selected>-- Pilih Kelas Asal --</option>
                            @foreach($fromClasses as $class)
                                <option value="{{ $class->id }}">
                                    {{ $class->nama_kelas }} ({{ $class->academicYear->name }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block font-semibold text-sm text-[#373C2E] mb-1">Ke Kelas (Tahun Ajaran Aktif)</label>
                        <select name="to_class_id" required
                                class="w-full px-4 py-2 border border-gray-300 rounded-md focus:ring focus:ring-blue-200">
                            <option disabled selected>-- Pilih Kelas Tujuan --</option>
                            @foreach($toClasses as $class)
                                <option value="{{ $class->id }}">
                                    {{ $class->nama_kelas }} ({{ $class->academicYear->name }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="pt-2">
                    <button type="submit"
                            class="inline-flex items-center gap-2 bg-blue-600 text-white px-5 py-2 rounded-md hover:bg-blue-700 shadow">
                        <i class="bi bi-arrow-right-circle-fill"></i> Promosikan
                    </button>
                </div>
            </form>

        </div>
    </div>
</x-app-layout>
