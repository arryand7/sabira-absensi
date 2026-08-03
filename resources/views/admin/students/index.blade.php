<x-app-shell>
<div class="mt-6 w-full sm:px-6 lg:px-8 space-y-6">
        <main class="flex-1 max-w-full overflow-x-auto">
            <!-- Tombol Aksi -->
            <div class="flex flex-wrap items-center justify-between mb-4 gap-4">
                <div class="flex flex-wrap items-center gap-4">
                    <a href="{{ route('admin.students.create') }}"
                        class="inline-block bg-[var(--sabira-primary)] hover:bg-[var(--sabira-primary-active)] text-white px-4 py-2 rounded shadow transition">
                        + Tambah Murid
                    </a>

                    <form id="bulk-delete-form" action="{{ route('admin.students.bulk-delete') }}" method="POST">
                        @csrf
                        <input type="hidden" name="student_ids_json" id="student_ids_json" />
                        <button type="submit"
                            class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded shadow transition"
                            onclick="return confirm('Yakin ingin menghapus murid terpilih?')">
                            Hapus Murid Terpilih
                        </button>
                    </form>
                </div>

                <!-- Tombol Import -->
                <form action="{{ route('admin.students.import') }}" method="POST" enctype="multipart/form-data"
                    class="flex items-center gap-3">
                    @csrf
                    <input type="file" name="file" required
                        class="border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 text-gray-900" />
                    <button type="submit"
                        class="bg-[var(--sabira-primary)] hover:bg-[var(--sabira-primary-active)] text-white px-4 py-2 rounded shadow transition">
                        Import Excel
                    </button>
                </form>
            </div>

            <!-- Table Card -->
            <div class="bg-[var(--sabira-surface)] shadow rounded-xl p-6 overflow-x-auto">

                <!-- Filter -->
                <form method="GET" action="{{ route('admin.students.index') }}"
                    class="flex flex-wrap gap-6 mb-6 items-end max-w-full">
                    <div class="flex flex-col">
                        <label for="kelas_formal" class="text-sm font-medium mb-1 text-gray-900">Kelas Formal</label>
                        <select id="kelas_formal" name="kelas_formal"
                            class="border border-gray-300 rounded px-3 py-2 w-48 focus:outline-none focus:ring-2 focus:ring-blue-500 text-gray-900">
                            <option value="">Semua</option>
                            @foreach ($academicClasses as $class)
                                <option value="{{ $class->id }}" {{ request('kelas_formal') == $class->id ? 'selected' : '' }}>
                                    {{ $class->nama_kelas }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="flex flex-col">
                        <label for="kelas_muadalah" class="text-sm font-medium mb-1 text-gray-900">Kelas Muadalah</label>
                        <select id="kelas_muadalah" name="kelas_muadalah"
                            class="border border-gray-300 rounded px-3 py-2 w-48 focus:outline-none focus:ring-2 focus:ring-blue-500 text-gray-900">
                            <option value="">Semua</option>
                            @foreach ($muadalahClasses as $class)
                                <option value="{{ $class->id }}" {{ request('kelas_muadalah') == $class->id ? 'selected' : '' }}>
                                    {{ $class->nama_kelas }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="flex flex-col">
                        <label for="kelas_tambahan" class="text-sm font-medium mb-1 text-gray-900">Kelas Tambahan</label>
                        <select id="kelas_tambahan" name="kelas_tambahan"
                            class="border border-gray-300 rounded px-3 py-2 w-48 focus:outline-none focus:ring-2 focus:ring-blue-500 text-gray-900">
                            <option value="">Semua</option>
                            @foreach ($tambahanClasses as $class)
                                <option value="{{ $class->id }}" {{ request('kelas_tambahan') == $class->id ? 'selected' : '' }}>
                                    {{ $class->nama_kelas }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="flex flex-col">
                        <label for="jenis_kelamin" class="text-sm font-medium mb-1 text-gray-900">Jenis Kelamin</label>
                        <select id="jenis_kelamin" name="jenis_kelamin"
                            class="border border-gray-300 rounded px-3 py-2 w-40 focus:outline-none focus:ring-2 focus:ring-blue-500 text-gray-900">
                            <option value="">Semua</option>
                            <option value="L" {{ request('jenis_kelamin') == 'L' ? 'selected' : '' }}>Laki-laki</option>
                            <option value="P" {{ request('jenis_kelamin') == 'P' ? 'selected' : '' }}>Perempuan</option>
                        </select>
                    </div>

                    <div class="flex items-center gap-2">
                        <button type="submit"
                            class="bg-[var(--sabira-primary)] hover:bg-[var(--sabira-primary-active)] text-white px-5 py-2 rounded shadow transition">
                            Filter
                        </button>
                        <a href="{{ route('admin.students.index') }}" class="text-gray-600 hover:underline">Reset</a>
                    </div>
                </form>

                <!-- Table -->
                <table id="studentTable" class="stripe hover w-full text-sm text-left text-gray-800">
                    <thead class="bg-[var(--sabira-neutral-strong)] text-white uppercase text-xs font-semibold">
                        <tr>
                            <th class="px-4 py-2">
                                <input type="checkbox" id="select-all" />
                            </th>
                            <th class="px-4 py-2">Nama</th>
                            <th class="px-4 py-2">NIS</th>
                            <th class="px-4 py-2">Kelas Reguler</th>
                            <th class="px-4 py-2">Kelas Non-Reguler</th>
                            <th class="px-4 py-2">Kelas Tambahan</th>
                            <th class="px-4 py-2">Jenis Kelamin</th>
                            <th class="px-4 py-2 text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#D6D8D2]">
                        @if ($students->count() > 0)
                            @foreach ($students as $student)
                                <tr class="hover:bg-[var(--sabira-surface-strong)] transition">
                                    <td class="px-4 py-2">
                                        <input type="checkbox" name="student_ids[]" value="{{ $student->id }}" class="student-checkbox" />
                                    </td>
                                    <td class="px-4 py-2">{{ $student->nama_lengkap }}</td>
                                    <td class="px-4 py-2">{{ $student->nis }}</td>
                                    <td class="px-4 py-2">{{ $student->classGroups->firstWhere('jenis_kelas', 'formal')?->nama_kelas ?? '-' }}</td>
                                    <td class="px-4 py-2">{{ $student->classGroups->firstWhere('jenis_kelas', 'muadalah')?->nama_kelas ?? '-' }}</td>
                                    <td class="px-4 py-2">{{ $student->classGroups->firstWhere('jenis_kelas', 'tambahan')?->nama_kelas ?? '-' }}</td>
                                    <td class="px-4 py-2">{{ $student->jenis_kelamin }}</td>
                                    <td class="px-4 py-2 space-x-2 text-center">
                                        <a href="{{ route('admin.students.edit', $student->id) }}"
                                            class="bg-yellow-500 text-white px-3 py-1 rounded hover:bg-yellow-600">
                                            Edit
                                        </a>
                                        <form action="{{ route('admin.students.destroy', $student->id) }}" method="POST" class="inline delete-form">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit"
                                                class="bg-red-600 text-white px-3 py-1 rounded hover:bg-red-700">
                                                Hapus
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        @else
                            <tr>
                                <td colspan="8" class="px-4 py-4 text-center text-gray-500 dark:text-gray-400">
                                    Belum ada data murid.
                                </td>
                            </tr>
                        @endif
                    </tbody>
                </table>
            </div>
        </main>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const checkboxes = Array.from(document.querySelectorAll('.student-checkbox'));
            document.getElementById('select-all')?.addEventListener('change', (event) => {
                checkboxes.forEach((checkbox) => checkbox.checked = event.target.checked);
            });

            document.getElementById('bulk-delete-form')?.addEventListener('submit', (event) => {
                const selectedIds = checkboxes.filter((checkbox) => checkbox.checked).map((checkbox) => checkbox.value);
                if (selectedIds.length === 0) {
                    event.preventDefault();
                    alert('Pilih minimal satu murid terlebih dahulu.');
                    return;
                }

                if (!confirm(`Hapus ${selectedIds.length} murid terpilih?`)) {
                    event.preventDefault();
                    return;
                }

                document.getElementById('student_ids_json').value = JSON.stringify(selectedIds);
            });

            document.querySelectorAll('.delete-form').forEach((form) => {
                form.addEventListener('submit', (event) => {
                    if (!confirm('Hapus murid ini? Data yang dihapus tidak dapat dikembalikan.')) {
                        event.preventDefault();
                    }
                });
            });
        });
    </script>

    @if($errors->any())
        <x-alert type="danger" title="Terjadi kesalahan">
            <ul class="list-disc pl-5">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </x-alert>
    @endif
</x-app-shell>
