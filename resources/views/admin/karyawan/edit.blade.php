<x-app-shell headerTitle="Edit Profil Pegawai" headerSubtitle="Perbarui divisi dan kontak pegawai">
    <div class="max-w-2xl mx-auto rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
        <div class="mb-6 flex items-center justify-between">
            <div><h2 class="text-base font-bold">{{ $karyawan->user->name }}</h2><p class="text-xs text-slate-500">{{ $karyawan->user->email }}</p></div>
            <a href="{{ route('karyawan.show', $karyawan) }}" class="text-xs font-semibold text-slate-500">Kembali</a>
        </div>
        <form method="POST" action="{{ route('karyawan.update', $karyawan) }}" class="space-y-4">
            @csrf @method('PUT')
            <div><label class="block text-xs font-semibold mb-1">Divisi</label><select name="divisi_id" class="w-full rounded-lg border-slate-300 text-sm"><option value="">Tanpa divisi</option>@foreach($divisis as $divisi)<option value="{{ $divisi->id }}" @selected(old('divisi_id', $karyawan->divisi_id) == $divisi->id)>{{ $divisi->nama }}</option>@endforeach</select></div>
            <div><label class="block text-xs font-semibold mb-1">Nomor HP</label><input name="no_hp" value="{{ old('no_hp', $karyawan->no_hp) }}" class="w-full rounded-lg border-slate-300 text-sm"></div>
            <div><label class="block text-xs font-semibold mb-1">Alamat</label><textarea name="alamat" rows="3" class="w-full rounded-lg border-slate-300 text-sm">{{ old('alamat', $karyawan->alamat) }}</textarea></div>
            <div class="flex justify-end"><button class="rounded-lg bg-[var(--sabira-primary)] px-4 py-2 text-xs font-bold text-white">Simpan Perubahan</button></div>
        </form>
    </div>
</x-app-shell>
