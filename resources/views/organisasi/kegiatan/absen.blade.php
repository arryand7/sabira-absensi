<x-user-layout>
    <div class="max-w-4xl mx-auto py-8 px-4 sm:px-6 lg:px-8">

        <div class="flex items-center justify-between mb-2">
            <h1 class="text-2xl font-bold text-[#292D22]">
                Absensi Kegiatan: {{ $kegiatan->kegiatanAsrama->nama }}
            </h1>
            <a href="{{ route('asrama.kegiatan') }}"
            class="inline-flex items-center gap-2 px-4 py-2 bg-[#5C644C] hover:bg-[#535A44] text-white rounded-md text-sm shadow transition">
                <i class="bi bi-arrow-left-circle"></i> Kembali
            </a>
        </div>


        <form id="absenForm" method="POST" action="{{ route('asrama.kegiatan.absen.submit', ['id' => $kegiatan->id]) }}" class="space-y-6 bg-[#EFF0ED] p-6 rounded-xl shadow-md border border-[#D6D8D2]" autocomplete="off">
            @csrf

            {{-- Input pencarian --}}
            <div>
                <label for="search" class="block text-sm font-medium text-[#44483B] mb-1">Cari NIS Siswa:</label>
                <input
                    type="text"
                    id="search"
                    name="search"
                    placeholder="Ketik NIS siswa..."
                    autocomplete="off"
                    class="w-full border border-[#BFC2B8] rounded-lg p-2 focus:ring-[#C6D2B2] focus:border-[#C6D2B2]"
                />
                <div id="searchResults" class="mt-2 border border-[#D6D8D2] rounded-lg max-h-40 overflow-y-auto bg-white shadow-md hidden z-10">
                    {{-- Hasil pencarian dimuat di sini --}}
                </div>
            </div>

            {{-- Daftar absensi --}}
            <div>
                <h3 class="text-lg font-semibold text-[#292D22] mb-3">Daftar Absensi</h3>
                <div id="absensiList" class="space-y-3 max-h-96 overflow-y-auto pr-1">
                    @foreach(\App\Models\Student::orderBy('nama_lengkap')->get() as $siswa)
                        <div class="flex items-center justify-between border border-[#D6D8D2] p-3 rounded-lg bg-white">
                            <span class="font-medium text-[#292D22]">{{ $siswa->nama_lengkap }} <span class="text-sm text-[#6C6F65]">({{ $siswa->nis }})</span></span>
                            <div class="text-sm {{ ($absensiHariIni[$siswa->id]->status ?? 'alpa') === 'hadir' ? 'text-green-600' : 'text-red-600' }} font-semibold" id="statusLabel_{{ $siswa->id }}">
                                ({{ $absensiHariIni[$siswa->id]->status ?? 'alpa' }})
                            </div>
                            <input type="hidden" name="students[{{ $siswa->id }}]" id="status_{{ $siswa->id }}" value="{{ $absensiHariIni[$siswa->id]->status ?? 'alpa' }}">
                        </div>
                    @endforeach
                </div>
            </div>

            {{-- Tombol submit --}}
            <div class="text-center">
                <button type="submit" class="px-6 py-2 bg-[#5C644C] text-white font-semibold rounded-lg shadow hover:bg-[#4B543F] transition">
                    Submit Absensi
                </button>
            </div>
        </form>
    </div>

    <script>
    document.addEventListener('DOMContentLoaded', function () {
        const searchInput = document.getElementById('search');
        const resultsDiv = document.getElementById('searchResults');
        const absensiList = document.getElementById('absensiList');

        let currentFocus = -1;
        let localStatusMap = {}; // untuk menyimpan perubahan status lokal

        function clearActive(items) {
            items.forEach(item => item.classList.remove('bg-[#D9DCD1]'));
        }

        function addActive(items, index) {
            if (!items || items.length === 0) return -1;
            clearActive(items);
            if (index >= items.length) index = 0;
            if (index < 0) index = items.length - 1;
            items[index].classList.add('bg-[#D9DCD1]');
            return index;
        }

        function toggleStatus(studentId) {
            const inputStatus = document.getElementById('status_' + studentId);
            const statusLabel = document.getElementById('statusLabel_' + studentId);
            const studentDiv = statusLabel.closest('.flex');

            if (inputStatus.value === 'hadir') {
                inputStatus.value = 'alpa';
                statusLabel.textContent = '(alpa)';
                statusLabel.classList.remove('text-green-600');
                statusLabel.classList.add('text-red-600');
                localStatusMap[studentId] = 'alpa';
            } else {
                inputStatus.value = 'hadir';
                statusLabel.textContent = '(hadir)';
                statusLabel.classList.remove('text-red-600');
                statusLabel.classList.add('text-green-600');
                absensiList.prepend(studentDiv);
                localStatusMap[studentId] = 'hadir';
            }
        }

        resultsDiv.addEventListener('click', function (e) {
            if (e.target.classList.contains('student')) {
                const studentId = e.target.getAttribute('data-id');
                toggleStatus(studentId);
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
                fetch(`{{ route('asrama.kegiatan.search', ['id' => $kegiatan->id]) }}?keyword=${encodeURIComponent(query)}`)
                    .then(response => response.json())
                    .then(data => {
                        resultsDiv.innerHTML = '';
                        if (data.length > 0) {
                            data.forEach(student => {
                                const currentStatus = localStatusMap[student.id] ?? student.status;
                                const statusColor = currentStatus === 'hadir' ? 'text-green-600' : 'text-red-600';
                                const div = document.createElement('div');
                                div.classList.add('student', 'px-4', 'py-2', 'hover:bg-[#E3E4DF]', 'cursor-pointer');
                                div.setAttribute('data-id', student.id);
                                div.innerHTML = `
                                    ${student.nis} - ${student.nama_lengkap} -
                                    <span class="${statusColor} font-semibold">(${currentStatus})</span>
                                `;

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

        searchInput.addEventListener('keydown', function (e) {
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

        // Klik label status langsung ubah
        document.querySelectorAll('[id^="statusLabel_"]').forEach(function(label) {
            label.addEventListener('click', function () {
                const studentId = this.id.replace('statusLabel_', '');
                toggleStatus(studentId);
            });
        });
    });
    </script>
</x-user-layout>
