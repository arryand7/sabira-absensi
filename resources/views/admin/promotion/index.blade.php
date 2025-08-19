<x-app-layout>
    <x-slot name="sidebar">
        <x-admin-sidenav />
    </x-slot>

    <h2 class="font-semibold text-xl text-[#292D22]">Migrasi / Pindah Siswa</h2>

    <div class="flex">
        <div class="mt-6 w-full sm:px-6 lg:px-8 space-y-6">
            <div class="bg-[#EEF3E9] shadow-md rounded-2xl p-6">

                {{-- Alert --}}
                @if(session('success'))
                    <div class="bg-green-100 text-green-800 p-3 rounded mb-4 text-sm">
                        {{ session('success') }}
                    </div>
                @endif

                @if(session('error'))
                    <div class="bg-red-100 text-red-800 p-3 rounded mb-4 text-sm">
                        {!! session('error') !!}
                    </div>
                @endif

                {{-- Dropdown Kelas --}}
                <form id="promotion-form" method="POST" action="{{ route('promotion.promote') }}" class="space-y-4">
                    @csrf
                    <div>
                        <label class="font-medium block mb-1 text-sm">Kelas Tujuan:</label>
                        <select name="to_class_id" required
                                class="w-full md:w-1/2 rounded border border-gray-300 px-3 py-2 bg-white">
                            <option value="">-- Pilih Kelas Tujuan --</option>
                            @foreach($toClasses as $class)
                                <option value="{{ $class->id }}">{{ $class->nama_kelas }}</option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Box Siswa Terpilih --}}
                    <div id="selected-students-box"
                         class="hidden bg-blue-50 p-3 rounded border border-blue-200 max-h-40 overflow-y-auto text-sm">
                        <h4 class="font-semibold mb-2">Siswa Terpilih:</h4>
                        <ul id="selected-students" class="list-disc ml-5 text-blue-800"></ul>
                    </div>

                    {{-- Tabel Siswa --}}
                    <div class="overflow-x-auto mt-4">
                        <table id="studentsTable" class="w-full table-auto text-left text-sm text-[#373C2E]">
                            <thead class="bg-[#8D9382] text-white uppercase text-xs font-semibold">
                                <tr>
                                    <th class="px-4 py-3 text-center"><input type="checkbox" id="selectAll"></th>
                                    <th class="px-4 py-3">Nama</th>
                                    <th class="px-4 py-3">NIS</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-[#D6D8D2]">
                                @foreach($students as $student)
                                    <tr class="hover:bg-[#BEC1B7] transition">
                                        <td class="px-4 py-2 text-center">
                                            <input type="checkbox" name="student_ids[]" value="{{ $student->id }}" data-name="{{ $student->nama_lengkap }}">
                                        </td>
                                        <td class="px-4 py-2">{{ $student->nama_lengkap }}</td>
                                        <td class="px-4 py-2">{{ $student->nis }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    {{-- Tombol --}}
                    <div class="mt-6 flex gap-3">
                        <button type="submit"
                                class="bg-blue-600 text-white px-4 py-2 rounded-md text-sm hover:bg-blue-700 shadow">
                            <i class="bi bi-arrow-right-circle"></i> Pindahkan Siswa
                        </button>
                        <a href="{{ route('academic-years.index') }}"
                           class="bg-gray-500 text-white px-4 py-2 rounded-md text-sm hover:bg-gray-600 shadow">
                            <i class="bi bi-x-circle"></i> Batal
                        </a>
                    </div>
                </form>

                {{-- Script --}}
                <script>
                    $(document).ready(function () {
                        let table = $('#studentsTable').DataTable({
                            pageLength: 10,
                            order: [[1, 'asc']],
                            language: {
                                search: "Cari:",
                                lengthMenu: "Tampilkan _MENU_ siswa",
                                info: "Menampilkan _START_ sampai _END_ dari _TOTAL_ siswa",
                                paginate: {
                                    first: "Awal",
                                    last: "Akhir",
                                    next: "Berikutnya",
                                    previous: "Sebelumnya"
                                },
                                zeroRecords: "Tidak ada siswa ditemukan",
                            }
                        });

                        const selectedBox = $('#selected-students-box');
                        const selectedList = $('#selected-students');
                        let selectedStudentMap = new Map();

                        function updateSelectedList() {
                            selectedList.empty();
                            if (selectedStudentMap.size > 0) {
                                selectedBox.removeClass('hidden');
                                selectedStudentMap.forEach(name => selectedList.append(`<li>${name}</li>`));
                            } else {
                                selectedBox.addClass('hidden');
                            }
                        }

                        $('#studentsTable tbody').on('change', 'input[name="student_ids[]"]', function () {
                            const id = $(this).val(), name = $(this).data('name');
                            $(this).is(':checked') ? selectedStudentMap.set(id, name) : selectedStudentMap.delete(id);
                            updateSelectedList();
                        });

                        $('#selectAll').on('change', function () {
                            const isChecked = $(this).is(':checked');
                            $('#studentsTable tbody input[name="student_ids[]"]').each(function () {
                                const id = $(this).val(), name = $(this).data('name');
                                $(this).prop('checked', isChecked);
                                isChecked ? selectedStudentMap.set(id, name) : selectedStudentMap.delete(id);
                            });
                            updateSelectedList();
                        });

                        table.on('draw', function () {
                            $('#studentsTable input[name="student_ids[]"]').each(function () {
                                const id = $(this).val();
                                $(this).prop('checked', selectedStudentMap.has(id));
                            });
                        });

                        $('#promotion-form').on('submit', function (e) {
                            const kelas = $('select[name="to_class_id"]').find(":selected").text();

                            if (selectedStudentMap.size === 0) {
                                alert("Pilih minimal satu siswa terlebih dahulu.");
                                e.preventDefault();
                                return;
                            }

                            if (!kelas || kelas === '-- Pilih Kelas Tujuan --') {
                                alert("Pilih kelas tujuan terlebih dahulu.");
                                e.preventDefault();
                                return;
                            }

                            const confirmMsg = `Anda akan memindahkan ${selectedStudentMap.size} siswa ke kelas "${kelas}". Lanjutkan?`;
                            if (!confirm(confirmMsg)) {
                                e.preventDefault();
                                return;
                            }
                        });
                    });
                </script>

            </div>
        </div>
    </div>
</x-app-layout>
