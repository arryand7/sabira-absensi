<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">
            Tambah Karyawan
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-3xl mx-auto bg-white p-6 rounded shadow">
            <form method="POST" action="{{ route('karyawan.store') }}">
                @csrf

                <div class="mb-4">
                    <label class="block font-medium text-sm text-gray-700">User Login</label>
                    <select name="user_id" class="w-full mt-1 border-gray-300 rounded">
                        @foreach ($users as $user)
                            <option value="{{ $user->id }}">{{ $user->name }} - {{ $user->email }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="mb-4">
                    <label class="block font-medium text-sm text-gray-700">Nama Lengkap</label>
                    <input type="text" name="nama_lengkap" class="w-full mt-1 border-gray-300 rounded" required>
                </div>

                <div class="mb-4">
                    <label class="block font-medium text-sm text-gray-700">Divisi</label>
                    <input type="text" name="divisi" class="w-full mt-1 border-gray-300 rounded" required>
                </div>

                <div class="mb-4">
                    <label class="block font-medium text-sm text-gray-700">Jabatan</label>
                    <input type="text" name="jabatan" class="w-full mt-1 border-gray-300 rounded" required>
                </div>

                <div class="mb-4">
                    <label class="block font-medium text-sm text-gray-700">Tanggal Masuk</label>
                    <input type="date" name="tanggal_masuk" class="w-full mt-1 border-gray-300 rounded">
                </div>

                <div class="mt-6">
                    <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
