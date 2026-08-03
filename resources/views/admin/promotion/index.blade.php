<x-app-shell>
<h2 class="font-semibold text-xl text-[var(--sabira-ink)]">Migrasi / Pindah Siswa</h2>

    <div class="flex">
        <div class="mt-6 w-full sm:px-6 lg:px-8 space-y-6">
            <div class="bg-[var(--sabira-surface)] shadow-md rounded-2xl p-6">

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
                        <table id="studentsTable" class="w-full table-auto text-left text-sm text-[var(--sabira-body)]">
                            <thead class="bg-[var(--sabira-neutral-strong)] text-white uppercase text-xs font-semibold">
                                <tr>
                                    <th class="px-4 py-3 text-center"><input type="checkbox" id="selectAll"></th>
                                    <th class="px-4 py-3">Nama</th>
                                    <th class="px-4 py-3">NIS</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-[#D6D8D2]">
                                @foreach($students as $student)
                                    <tr class="hover:bg-[var(--sabira-surface-strong)] transition">
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
                                class="bg-[var(--sabira-primary)] text-white px-4 py-2 rounded-md text-sm hover:bg-[var(--sabira-primary-active)] shadow">
                            <i class="bi bi-arrow-right-circle"></i> Pindahkan Siswa
                        </button>
                        <a href="{{ route('academic-years.index') }}"
                           class="bg-gray-500 text-white px-4 py-2 rounded-md text-sm hover:bg-gray-600 shadow">
                            <i class="bi bi-x-circle"></i> Batal
                        </a>
                    </div>
                </form>

                <script>
                    document.addEventListener('DOMContentLoaded', () => {
                        const selectedBox = document.getElementById('selected-students-box');
                        const selectedList = document.getElementById('selected-students');
                        const checkboxes = Array.from(document.querySelectorAll('#studentsTable input[name="student_ids[]"]'));

                        const updateSelectedList = () => {
                            const selected = checkboxes.filter((checkbox) => checkbox.checked);
                            selectedList.replaceChildren(...selected.map((checkbox) => {
                                const item = document.createElement('li');
                                item.textContent = checkbox.dataset.name;
                                return item;
                            }));
                            selectedBox.classList.toggle('hidden', selected.length === 0);
                        };

                        checkboxes.forEach((checkbox) => checkbox.addEventListener('change', updateSelectedList));
                        document.getElementById('selectAll')?.addEventListener('change', (event) => {
                            checkboxes.forEach((checkbox) => checkbox.checked = event.target.checked);
                            updateSelectedList();
                        });

                        document.getElementById('promotion-form')?.addEventListener('submit', (event) => {
                            const selected = checkboxes.filter((checkbox) => checkbox.checked);
                            const target = document.querySelector('select[name="to_class_id"]');
                            const targetName = target?.selectedOptions[0]?.textContent?.trim();

                            if (selected.length === 0) {
                                event.preventDefault();
                                alert('Pilih minimal satu siswa terlebih dahulu.');
                                return;
                            }
                            if (!target?.value) {
                                event.preventDefault();
                                alert('Pilih kelas tujuan terlebih dahulu.');
                                return;
                            }
                            if (!confirm(`Pindahkan ${selected.length} siswa ke kelas "${targetName}"?`)) {
                                event.preventDefault();
                            }
                        });
                    });
                </script>

            </div>
        </div>
    </div>
</x-app-shell>
