<x-app-layout>
    <div class="max-w-4xl mx-auto py-8 px-4 sm:px-6 lg:px-8 bg-[#F7F7F6]">
        <h1 class="text-2xl font-bold text-[#292D22] mb-6">
            <i class="bi bi-journal-check mr-2 text-[#5C644C]"></i>
            Absensi Sholat: <span class="capitalize">{{ $jenis }}</span>
        </h1>

        <form id="absenForm" method="POST" action="{{ route('asrama.sholat.submit', ['jenis' => $jenis]) }}"
              class="space-y-6 bg-[#EFF0ED] p-6 rounded-xl border border-[#D6D8D2] shadow-sm"
              autocomplete="off">
            @csrf

            {{-- Input Search NIS --}}
            <div>
                <label for="search" class="block text-sm font-medium text-[#44483B] mb-1">Cari NIS Siswa:</label>
                <input
                    type="text"
                    id="search"
                    name="search"
                    placeholder="Ketik NIS siswa..."
                    class="w-full border border-[#BFC2B8] rounded-lg p-2 focus:ring focus:ring-[#C6D2B2]"
                />
                <div id="searchResults"
                     class="mt-2 border border-[#BFC2B8] rounded-lg max-h-40 overflow-y-auto bg-white shadow hidden z-10">
                    {{-- Hasil pencarian dimuat dengan JS --}}
                </div>
            </div>

            {{-- Daftar Absensi --}}
            <div>
                <h3 class="text-lg font-semibold text-[#292D22] mb-3">Daftar Absensi</h3>
                <div id="absensiList" class="space-y-3 max-h-96 overflow-y-auto pr-1">
                    @foreach(\App\Models\Student::orderBy('nama_lengkap')->get() as $siswa)
                        <div class="flex items-center justify-between border border-[#D6D8D2] p-3 rounded-lg bg-white shadow-sm">
                            <span class="font-medium text-[#1C1E17]">{{ $siswa->nama_lengkap }}</span>
                            <div class="text-sm text-red-600 font-semibold" id="statusLabel_{{ $siswa->id }}">(alpa)</div>
                            <input type="hidden" name="students[{{ $siswa->id }}]" id="status_{{ $siswa->id }}" value="alpa">
                        </div>
                    @endforeach
                </div>
            </div>

            {{-- Tombol Submit --}}
            <div class="text-center pt-4">
                <button type="submit"
                        class="inline-flex items-center px-6 py-2 bg-[#5C644C] text-white font-semibold rounded-lg shadow hover:bg-[#49543B] transition">
                    <i class="bi bi-save2-fill mr-2"></i> Submit Absensi
                </button>
            </div>
        </form>
    </div>

    {{-- Bootstrap Icons --}}
    {{-- <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet"> --}}

    <script>
    document.addEventListener('DOMContentLoaded', function () {
        const searchInput = document.getElementById('search');
        const resultsDiv = document.getElementById('searchResults');
        const absensiList = document.getElementById('absensiList');

        let currentFocus = -1;

        function clearActive(items) {
            items.forEach(item => item.classList.remove('bg-[#D9DCD1]'));
        }

        function addActive(items, index) {
            if (!items || items.length === 0) return;
            clearActive(items);
            if (index >= items.length) index = 0;
            if (index < 0) index = items.length - 1;
            items[index].classList.add('bg-[#D9DCD1]');
            return index;
        }

        function setHadir(studentId, studentName) {
            const inputStatus = document.getElementById('status_' + studentId);
            const statusLabel = document.getElementById('statusLabel_' + studentId);
            const studentDiv = statusLabel.closest('.flex');

            if (inputStatus && inputStatus.value !== 'hadir') {
                inputStatus.value = 'hadir';
                statusLabel.textContent = '(hadir)';
                statusLabel.classList.remove('text-red-600');
                statusLabel.classList.add('text-green-600', 'font-semibold');
                absensiList.prepend(studentDiv);
            }
        }

        resultsDiv.addEventListener('click', function(e) {
            if(e.target.classList.contains('student')) {
                const studentId = e.target.getAttribute('data-id');
                const studentName = e.target.textContent.trim();
                setHadir(studentId, studentName);
                searchInput.value = '';
                resultsDiv.classList.add('hidden');
                resultsDiv.innerHTML = '';
                currentFocus = -1;
            }
        });

        searchInput.addEventListener('input', function () {
            const query = this.value.trim();
            currentFocus = -1;

            if (query.length >= 3) {
                fetch(`{{ route('asrama.sholat.search', ['jenis' => $jenis]) }}?keyword=${encodeURIComponent(query)}`)
                    .then(response => response.json())
                    .then(data => {
                        resultsDiv.innerHTML = '';
                        if (data.length > 0) {
                            data.forEach(student => {
                                const div = document.createElement('div');
                                div.classList.add('student', 'px-4', 'py-2', 'hover:bg-[#E3E4DF]', 'cursor-pointer');
                                div.setAttribute('data-id', student.id);
                                div.textContent = student.nis + ' - ' + student.nama_lengkap;
                                resultsDiv.appendChild(div);
                            });
                            resultsDiv.classList.remove('hidden');
                        } else {
                            resultsDiv.innerHTML = '<div class="px-4 py-2 text-gray-500">Tidak ada hasil.</div>';
                            resultsDiv.classList.remove('hidden');
                        }
                    });
            } else {
                resultsDiv.classList.add('hidden');
                resultsDiv.innerHTML = '';
            }
        });

        searchInput.addEventListener('keydown', function(e) {
            let items = resultsDiv.querySelectorAll('.student');

            if (e.key === 'ArrowDown') {
                currentFocus++;
                currentFocus = addActive(items, currentFocus);
                e.preventDefault();
            } else if (e.key === 'ArrowUp') {
                currentFocus--;
                currentFocus = addActive(items, currentFocus);
                e.preventDefault();
            } else if (e.key === 'Enter') {
                e.preventDefault();
                if (items.length > 0 && currentFocus > -1) {
                    items[currentFocus].click();
                } else if (items.length > 0) {
                    items[0].click();
                }
            }
        });

        // Klik label (alpa) ubah ke hadir
        document.querySelectorAll('[id^="statusLabel_"]').forEach(function(label) {
            label.addEventListener('click', function () {
                const studentId = this.id.replace('statusLabel_', '');
                const studentName = this.closest('.flex').querySelector('span').textContent.trim();
                setHadir(studentId, studentName);
            });
        });
    });
    </script>

</x-app-layout>
