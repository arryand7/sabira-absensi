<div class="grid grid-cols-1 md:grid-cols-2 gap-6">
    {{-- Grid Kiri --}}
    <div class="bg-gray-100 p-4 rounded">
        <form wire:submit.prevent>
            <input
                type="text"
                wire:model="search"
                placeholder="Cari siswa... (min. 2 huruf)"
                class="w-full mt-2 p-1 rounded"
            />
        </form>

        @if(strlen($search) > 1 && empty($availableStudents))
            <div class="text-sm text-gray-500 mt-1">Tidak ada siswa ditemukan.</div>
        @endif

        <table class="w-full mt-2 text-sm">
            <thead>
                <tr><th>Nama</th><th>NIS</th><th></th></tr>
            </thead>
            <tbody>
            @foreach($availableStudents as $student)
                <tr wire:key="available-{{ $student['id'] }}">
                    <td>{{ $student['name'] }}</td>
                    <td>{{ $student['nis'] }}</td>
                    <td>
                        <button type="button" wire:click="addStudent({{ $student['id'] }})">➡️</button>
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>

    {{-- Grid Kanan --}}
    <div class="bg-gray-100 p-4 rounded">
        <label class="font-semibold">Kelas Tujuan:</label>
        <select wire:model="toClassId" class="w-full mt-1 mb-2 rounded p-1">
            <option>-- Pilih Kelas Tujuan --</option>
            @foreach($toClasses as $class)
                <option value="{{ $class->id }}">{{ $class->nama_kelas }}</option>
            @endforeach
        </select>

        <table class="w-full mt-2 text-sm">
            <thead><tr><th>Nama</th><th>NIS</th><th></th></tr></thead>
            <tbody>
            @foreach($selectedStudents as $student)
                <tr wire:key="selected-{{ $student['id'] }}">
                    <td>{{ $student['name'] }}</td>
                    <td>{{ $student['nis'] }}</td>
                    <td><button type="button" wire:click="removeStudent({{ $student['id'] }})">⬅️</button></td>
                </tr>
            @endforeach
            </tbody>
        </table>

        <button class="bg-blue-600 text-white mt-3 px-4 py-2 rounded" wire:click="promoteStudents">Pindah Siswa</button>
    </div>
</div>

@if (session()->has('success'))
    <div class="mt-4 p-3 bg-green-100 text-green-800 rounded">
        {{ session('success') }}
    </div>
@endif
@if (session()->has('error'))
    <div class="mt-4 p-3 bg-red-100 text-red-800 rounded">
        {{ session('error') }}
    </div>
@endif
