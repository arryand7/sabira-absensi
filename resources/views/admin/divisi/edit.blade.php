<x-app-shell>
<div class="flex justify-center w-full mt-6 sm:px-6 lg:px-8">
        <div class="bg-[var(--sabira-neutral-strong)] shadow-md rounded-xl p-6 w-full max-w-xl text-[var(--sabira-ink)]">
            <h1 class="text-2xl font-bold mb-4">Edit Divisi</h1>

            <form action="{{ route('divisis.update', $divisi) }}" method="POST" class="space-y-4">
                @csrf
                @method('PUT')

                <div>
                    <label for="nama" class="block font-medium mb-1">Nama Divisi</label>
                    <input type="text" name="nama" id="nama"
                        class="w-full rounded border border-gray-300 px-3 py-2 bg-[var(--sabira-surface)]
                               @error('nama') border-red-500 @enderror"
                        value="{{ old('nama', $divisi->nama) }}" required>
                    @error('nama')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div class="flex gap-4 mt-6">
                    <button type="submit"
                        class="bg-[var(--sabira-primary)] text-white px-4 py-2 rounded-md text-xs hover:bg-[var(--sabira-primary-active)] shadow">
                        <i class="bi bi-arrow-repeat"></i> Update
                    </button>
                    <a href="{{ route('divisis.index') }}"
                        class="bg-[var(--sabira-primary)] text-white px-4 py-2 rounded-md text-xs hover:bg-[var(--sabira-primary-active)] shadow inline-flex items-center">
                        <i class="bi bi-x-circle"></i> Batal
                    </a>
                </div>
            </form>
        </div>
    </div>
</x-app-shell>
