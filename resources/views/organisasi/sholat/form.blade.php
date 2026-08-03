<x-app-shell>
    <div class="max-w-4xl mx-auto py-8 px-4 sm:px-6 lg:px-8 bg-[var(--sabira-surface-soft)]">
        <div class="flex items-center justify-between mb-2">
            <h1 class="text-2xl font-bold text-[var(--sabira-ink)]">
                <i class="bi bi-journal-check mr-2 text-[var(--sabira-muted)]"></i>
                Absensi Sholat: <span class="capitalize">{{ $jenis }}</span>
            </h1>
            <a href="{{ route('asrama.sholat') }}"
            class="inline-flex items-center gap-2 px-4 py-2 bg-[var(--sabira-primary)] hover:bg-[var(--sabira-primary-active)] text-white rounded-md text-sm shadow transition">
                <i class="bi bi-arrow-left-circle"></i> Kembali
            </a>
        </div>

        {{-- Input Search NIS --}}
        <div class="mb-6 bg-[var(--sabira-surface-soft)] p-6 rounded-xl border border-[var(--sabira-border)] shadow-sm">
            <label for="search" class="block text-sm font-medium text-[var(--sabira-body)] mb-1">Cari NIS Siswa:</label>
            <input
                type="text"
                id="search"
                name="search"
                placeholder="Ketik NIS siswa..."
                class="w-full border border-[var(--sabira-border)] rounded-lg p-2 focus:ring-[#C6D2B2] focus:border-[var(--sabira-border)]"
            />
            <div id="searchResults"
                 class="mt-2 border border-[var(--sabira-border)] rounded-lg max-h-40 overflow-y-auto bg-white shadow hidden z-10">
                {{-- Hasil pencarian dimuat dengan JS --}}
            </div>
        </div>

        {{-- Daftar Absensi --}}
        <div>
            <h3 class="text-lg font-semibold text-[var(--sabira-ink)] mb-3">Daftar Absensi</h3>
            <div id="absensiList" class="space-y-3 max-h-96 overflow-y-auto pr-1">
                @foreach(\App\Models\Student::orderBy('nama_lengkap')->get() as $siswa)
                    <div class="flex items-center justify-between border border-[var(--sabira-border)] p-3 rounded-lg bg-white">
                        <span class="font-medium text-[var(--sabira-ink)]">{{ $siswa->nama_lengkap }}
                            <span class="text-sm text-[var(--sabira-muted)]">({{ $siswa->nis }})</span>
                        </span>
                        <div
                            class="text-sm {{ ($absensiHariIni[$siswa->id]->status ?? 'alpa') === 'hadir' ? 'text-green-600' : 'text-red-600' }} font-semibold cursor-pointer"
                            id="statusLabel_{{ $siswa->id }}">
                            ({{ $absensiHariIni[$siswa->id]->status ?? 'alpa' }})
                        </div>
                        <input type="hidden" id="status_{{ $siswa->id }}"
                               value="{{ $absensiHariIni[$siswa->id]->status ?? 'alpa' }}">
                    </div>
                @endforeach
            </div>
        </div>

        <div class="text-center pt-4">
            <a href="{{ route('asrama.sholat') }}"
            class="inline-flex items-center px-6 py-2 bg-[var(--sabira-primary)] text-white font-semibold rounded-lg shadow hover:bg-[var(--sabira-primary)] transition">
                <i class="bi bi-check-circle-fill mr-2"></i> Selesai
            </a>
        </div>
    </div>

    <script>
    document.addEventListener('DOMContentLoaded', function () {
        const searchInput = document.getElementById('search');
        const resultsDiv = document.getElementById('searchResults');
        const absensiList = document.getElementById('absensiList');
        let currentFocus = -1;
        let localStatusMap = {};

        function clearActive(items) {
            items.forEach(item => item.classList.remove('bg-[var(--sabira-surface-strong)]'));
        }

        function addActive(items, index) {
            if (!items || items.length === 0) return -1;
            clearActive(items);
            if (index >= items.length) index = 0;
            if (index < 0) index = items.length - 1;
            items[index].classList.add('bg-[var(--sabira-surface-strong)]');
            return index;
        }

        function toggleStatus(studentId) {
            const inputStatus = document.getElementById('status_' + studentId);
            const statusLabel = document.getElementById('statusLabel_' + studentId);
            const studentDiv = statusLabel.closest('.flex');
            let newStatus = (inputStatus.value === 'hadir') ? 'alpa' : 'hadir';

            fetch('{{ route('asrama.sholat.absen.update') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({
                    student_id: studentId,
                    status: newStatus,
                    jadwal_id: '{{ $jadwal->id }}'
                })
            }).then(res => res.json())
              .then(data => {
                if (data.success) {
                    inputStatus.value = newStatus;
                    statusLabel.textContent = `(${newStatus})`;
                    if (newStatus === 'hadir') {
                        statusLabel.classList.remove('text-red-600');
                        statusLabel.classList.add('text-green-600');
                        absensiList.prepend(studentDiv);
                    } else {
                        statusLabel.classList.remove('text-green-600');
                        statusLabel.classList.add('text-red-600');
                    }
                } else {
                    alert('Gagal update absen.');
                }
              }).catch(() => {
                alert('Gagal update absen. Cek koneksi.');
              });
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
                fetch(`{{ route('asrama.sholat.search', ['jenis' => $jenis]) }}?keyword=${encodeURIComponent(query)}`)
                    .then(response => response.json())
                    .then(data => {
                        resultsDiv.innerHTML = '';
                        if (data.length > 0) {
                            data.forEach(student => {
                                const currentStatus = localStatusMap[student.id] ?? student.status;
                                const statusColor = currentStatus === 'hadir' ? 'text-green-600' : 'text-red-600';
                                const div = document.createElement('div');
                                div.classList.add('student', 'px-4', 'py-2', 'hover:bg-[var(--sabira-surface-strong)]', 'cursor-pointer');
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

        document.querySelectorAll('[id^="statusLabel_"]').forEach(function (label) {
            label.addEventListener('click', function () {
                const studentId = this.id.replace('statusLabel_', '');
                toggleStatus(studentId);
            });
        });
    });
    </script>
</x-app-shell>
