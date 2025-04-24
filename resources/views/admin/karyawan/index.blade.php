<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Data Karyawan') }}
        </h2>
    </x-slot>

    <div class="py-10">

        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 bg-white dark:bg-gray-800 p-6 rounded-lg shadow">
            <a href="{{ route('laporan.karyawan') }}"
            class="mb-4 inline-block bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">
             + Tambah Karyawan
         </a>

            <table class="w-full table-auto text-left text-sm text-gray-500 dark:text-gray-400">
                <thead>
                    <tr>
                        <th class="px-4 py-2">Nama</th>
                        <th class="px-4 py-2">Email</th>
                        <th class="px-4 py-2">Divisi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($karyawans as $karyawan)
                        <tr class="border-b">
                            <td class="px-4 py-2">{{ $karyawan->user->name }}</td>
                            <td class="px-4 py-2">{{ $karyawan->user->email }}</td>
                            <td class="px-4 py-2">{{ $karyawan->divisi ?? '-' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</x-app-layout>
