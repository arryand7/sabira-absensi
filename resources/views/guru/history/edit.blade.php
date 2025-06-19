<x-app-layout>
    <div class="py-6 max-w-5xl mx-auto">
        <h2 class="text-2xl font-bold mb-6 text-[#292D22]">Edit Absensi</h2>

        @if ($errors->any())
            <div class="mb-4 p-4 bg-red-100 border border-red-200 text-red-700 rounded-lg">
                <ul class="list-disc list-inside text-sm">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('guru.history.update', [$absensi[0]->schedule_id, $absensi[0]->pertemuan]) }}">
            @csrf

            <div class="bg-[#EFF0ED] border border-[#D6D8D2] rounded-lg p-4 mb-6 text-sm text-[#1C1E17] space-y-2">
                <p>
                    <i class="bi bi-journal-text mr-2 text-[#5C644C]"></i>
                    <strong>Mapel:</strong> {{ $absensi[0]->schedule->subject->nama_mapel }}
                </p>
                <p>
                    <i class="bi bi-people-fill mr-2 text-[#5C644C]"></i>
                    <strong>Kelas:</strong> {{ $absensi[0]->schedule->classGroup->nama_kelas }}
                </p>
            </div>

            <div class="grid md:grid-cols-2 gap-4 mb-6">
                <div>
                    <label class="block text-sm font-medium text-[#373C2E] mb-1">Pertemuan Ke-</label>
                    <input type="number" name="pertemuan"
                        class="w-full border border-[#D6D8D2] rounded px-3 py-2 text-[#1C1E17]"
                        value="{{ $absensi[0]->pertemuan }}">
                </div>
                <div>
                    <label class="block text-sm font-medium text-[#373C2E] mb-1">Materi</label>
                    <input type="text" name="materi"
                        class="w-full border border-[#D6D8D2] rounded px-3 py-2 text-[#1C1E17]"
                        value="{{ $absensi[0]->materi }}">
                </div>
            </div>

            <div class="overflow-x-auto bg-[#F7F7F6] border border-[#D6D8D2] rounded-lg p-4">
                <table class="min-w-full text-sm">
                    <thead class="bg-[#5C644C] text-[#F7F7F6]">
                        <tr>
                            <th class="px-4 py-2 text-left">Nama</th>
                            <th class="px-2 py-2 text-center">Hadir</th>
                            <th class="px-2 py-2 text-center">Alpa</th>
                            <th class="px-2 py-2 text-center">Sakit</th>
                            <th class="px-2 py-2 text-center">Izin</th>
                        </tr>
                    </thead>
                    <tbody class="text-[#292D22]">
                        @foreach ($absensi as $item)
                            <tr class="border-t border-[#D6D8D2] hover:bg-[#EFF0ED]">
                                <td class="px-4 py-2">{{ $item->student->nama_lengkap }}</td>
                                @foreach (['hadir', 'alpa', 'sakit', 'izin'] as $status)
                                    <td class="text-center px-2">
                                        <input type="radio" name="attendance[{{ $item->student_id }}]"
                                               value="{{ $status }}"
                                               {{ $item->status == $status ? 'checked' : '' }}>
                                    </td>
                                @endforeach
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="mt-6 text-center">
                <button type="submit"
                    class="bg-[#5C644C] hover:bg-[#535A44] text-white px-6 py-2 rounded font-semibold transition">
                    <i class="bi bi-save me-2"></i> Simpan Perubahan
                </button>
            </div>
        </form>
    </div>
</x-app-layout>
