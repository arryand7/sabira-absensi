<x-app-layout>
    <x-slot name="sidebar">
        <x-admin-sidenav />
    </x-slot>

    <div class="p-4">
        <h2 class="text-xl font-bold mb-4">Migrasi / Pindah Siswa</h2>

        @if(session('success'))
            <div class="bg-green-100 text-green-800 p-2 rounded mb-4">
                {{ session('success') }}
            </div>
        @endif

        @if(session('error'))
            <div class="bg-red-100 text-red-800 p-2 rounded mb-4">
                {!! session('error') !!}
            </div>
        @endif


        {{-- Box Siswa Terpilih --}}
        <div id="selected-students-box" class="mb-4 hidden bg-blue-50 p-3 rounded border border-blue-200 max-h-40 overflow-y-auto">
            <h4 class="font-semibold mb-2">Siswa Terpilih:</h4>
            <ul id="selected-students" class="list-disc ml-5 text-sm text-blue-800"></ul>
        </div>

        <form id="promotion-form" method="POST" action="{{ route('promotion.promote') }}">
            @csrf

            {{-- Dropdown kelas --}}
            <div class="mb-4">
                <label class="font-semibold">Kelas Tujuan:</label>
                <select name="to_class_id" class="rounded p-1 border w-full md:w-1/2 mt-1" required>
                    <option value="">-- Pilih Kelas Tujuan --</option>
                    @foreach($toClasses as $class)
                        <option value="{{ $class->id }}">{{ $class->nama_kelas }}</option>
                    @endforeach
                </select>
            </div>

            {{-- DataTable siswa --}}
            <div class="overflow-x-auto">
                <table id="studentsTable" class="stripe w-full text-sm">
                    <thead class="bg-gray-200">
                        <tr>
                            <th><input type="checkbox" id="selectAll"></th>
                            <th>Nama</th>
                            <th>NIS</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($students as $student)
                            <tr>
                                <td class="text-center">
                                    <input type="checkbox" name="student_ids[]" value="{{ $student->id }}" data-name="{{ $student->nama_lengkap }}">
                                </td>
                                <td>{{ $student->nama_lengkap }}</td>
                                <td>{{ $student->nis }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <button type="submit" class="mt-4 px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">
                Pindahkan Siswa
            </button>
        </form>
    </div>

    {{-- DataTables + Interactivity --}}
    @push('scripts')
        {{-- jQuery & DataTables --}}
        {{-- <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
        <link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/jquery.dataTables.min.css">
        <script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script> --}}

        <script>
            $(document).ready(function () {
                let table = $('#studentsTable').DataTable({
                    pageLength: 10,
                    order: [[1, 'asc']],
                });

                const selectedBox = $('#selected-students-box');
                const selectedList = $('#selected-students');

                // ⏺️ Map untuk menyimpan semua siswa yang dicentang
                let selectedStudentMap = new Map();

                function updateSelectedList() {
                    selectedList.empty();

                    if (selectedStudentMap.size > 0) {
                        selectedBox.removeClass('hidden');

                        selectedStudentMap.forEach((name, id) => {
                            selectedList.append(`<li>${name}</li>`);
                        });
                    } else {
                        selectedBox.addClass('hidden');
                    }
                }

                // Cek / uncek individual
                $('#studentsTable tbody').on('change', 'input[name="student_ids[]"]', function () {
                    const studentId = $(this).val();
                    const studentName = $(this).data('name');

                    if ($(this).is(':checked')) {
                        selectedStudentMap.set(studentId, studentName);
                    } else {
                        selectedStudentMap.delete(studentId);
                    }

                    updateSelectedList();
                });

                // Select all di halaman saat ini
                $('#selectAll').on('change', function () {
                    const isChecked = $(this).is(':checked');

                    $('#studentsTable tbody input[name="student_ids[]"]').each(function () {
                        const studentId = $(this).val();
                        const studentName = $(this).data('name');

                        $(this).prop('checked', isChecked);

                        if (isChecked) {
                            selectedStudentMap.set(studentId, studentName);
                        } else {
                            selectedStudentMap.delete(studentId);
                        }
                    });

                    updateSelectedList();
                });

                // Saat redraw (pagination, search, dll), set checkbox sesuai array kita
                table.on('draw', function () {
                    $('#studentsTable input[name="student_ids[]"]').each(function () {
                        const studentId = $(this).val();
                        $(this).prop('checked', selectedStudentMap.has(studentId));
                    });
                });

                // Saat submit, buat hidden input untuk semua siswa yang dipilih
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

                    // Tambahkan input hidden untuk semua ID yang dipilih
                    selectedStudentMap.forEach((_, id) => {
                        $(this).append(`<input type="hidden" name="student_ids[]" value="${id}">`);
                    });
                });
            });
        </script>

    @endpush
</x-app-layout>
