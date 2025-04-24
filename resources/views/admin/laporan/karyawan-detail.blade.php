<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Detail Absensi - ' . $user->name) }}
        </h2>
    </x-slot>

    <div class="py-10">
        <div class="max-w-4xl mx-auto bg-white dark:bg-gray-800 p-6 rounded-lg shadow">
            <table class="w-full table-auto text-left text-sm text-gray-500 dark:text-gray-400">
                <thead>
                    <tr>
                        <th class="px-4 py-2">Tanggal</th>
                        <th class="px-4 py-2">Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($absensi as $a)
                        <tr class="border-b">
                            <td class="px-4 py-2">{{ $a->tanggal }}</td>
                            <td class="px-4 py-2">{{ ucfirst($a->status) }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td class="px-4 py-2" colspan="2">Belum ada data absensi</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</x-app-layout>
