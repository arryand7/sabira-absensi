<x-app-shell header-title="Edit Kehadiran Kerja" header-subtitle="Perbarui waktu masuk dan pulang pegawai">
    <div class="mx-auto max-w-2xl space-y-6">
        <x-breadcrumb :items="[
            ['label' => 'Dashboard', 'route' => 'admin.dashboard'],
            ['label' => 'Edit Kehadiran'],
        ]" />

        <section class="sabira-card" aria-labelledby="attendance-edit-title">
            <div class="sabira-card-header">
                <div>
                    <h1 id="attendance-edit-title" class="sabira-card-title">{{ $absen->user->name }}</h1>
                    <p class="sabira-card-subtitle">
                        {{ $absen->waktu_absen ? \Illuminate\Support\Carbon::parse($absen->waktu_absen)->translatedFormat('l, d F Y') : 'Tanggal tidak tersedia' }}
                    </p>
                </div>
                <x-status-badge :status="$absen->status" />
            </div>

            <form method="POST" action="{{ route('admin.absensi.update', $absen) }}" class="space-y-5">
                @csrf
                @method('PUT')

                <div class="grid gap-5 sm:grid-cols-2">
                    <div>
                        <x-input-label for="check_in" value="Waktu masuk" />
                        <x-text-input id="check_in" name="check_in" type="time" class="mt-1 block w-full"
                            :value="old('check_in', $absen->check_in)" />
                        <x-input-error :messages="$errors->get('check_in')" class="mt-2" />
                    </div>
                    <div>
                        <x-input-label for="check_out" value="Waktu pulang" />
                        <x-text-input id="check_out" name="check_out" type="time" class="mt-1 block w-full"
                            :value="old('check_out', $absen->check_out)" />
                        <x-input-error :messages="$errors->get('check_out')" class="mt-2" />
                    </div>
                </div>

                <div class="flex flex-wrap justify-end gap-3 border-t border-[var(--sabira-border-soft)] pt-5">
                    <x-button href="{{ route('admin.dashboard') }}" variant="secondary">Batal</x-button>
                    <x-button type="submit" variant="primary"><i class="fas fa-floppy-disk" aria-hidden="true"></i> Simpan perubahan</x-button>
                </div>
            </form>
        </section>
    </div>
</x-app-shell>
