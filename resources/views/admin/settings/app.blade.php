<x-app-layout>
    <div class="sm:px-6 lg:px-8">
        <x-page-title title="PENGATURAN APLIKASI" />
    </div>

    <x-slot name="sidebar">
        <x-admin-sidenav />
    </x-slot>

    <div class="mt-6 w-full sm:px-6 lg:px-8 space-y-6">
        <div class="bg-[#EEF3E9] shadow-md rounded-2xl p-6">
            <form action="{{ route('admin.settings.app.update') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
                @csrf
                @method('PUT')

                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Nama Aplikasi</label>
                        <input type="text" name="app_name" value="{{ old('app_name', $setting->app_name) }}"
                               class="mt-1 w-full rounded-md border-gray-300 shadow-sm focus:ring focus:ring-orange-200">
                        @error('app_name')
                            <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Deskripsi</label>
                        <textarea name="app_description" rows="4"
                                  class="mt-1 w-full rounded-md border-gray-300 shadow-sm focus:ring focus:ring-orange-200">{{ old('app_description', $setting->app_description) }}</textarea>
                        @error('app_description')
                            <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Logo Aplikasi</label>
                        <div class="mt-2 flex items-center gap-4">
                            @php
                                $logoUrl = $setting->app_logo
                                    ? asset('storage/' . $setting->app_logo)
                                    : asset('images/logo.png');
                            @endphp
                            <img src="{{ $logoUrl }}" alt="Logo" class="h-14 w-14 rounded-full object-cover border border-gray-200">
                            <input type="file" name="app_logo" accept=".jpg,.jpeg,.png,.webp"
                                   class="block w-full text-sm text-gray-700 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-[#8E412E] file:text-white hover:file:bg-[#BA6F4D]">
                        </div>
                        <p class="text-xs text-gray-500 mt-2">Format PNG/JPG/WEBP, maksimal 2MB.</p>
                        @error('app_logo')
                            <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Favicon</label>
                        <div class="mt-2 flex items-center gap-4">
                            @php
                                $faviconUrl = $setting->app_favicon
                                    ? asset('storage/' . $setting->app_favicon)
                                    : asset('images/logo.png');
                            @endphp
                            <img src="{{ $faviconUrl }}" alt="Favicon" class="h-12 w-12 rounded-md object-cover border border-gray-200">
                            <input type="file" name="app_favicon" accept=".jpg,.jpeg,.png,.webp,.ico"
                                   class="block w-full text-sm text-gray-700 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-[#8E412E] file:text-white hover:file:bg-[#BA6F4D]">
                        </div>
                        <p class="text-xs text-gray-500 mt-2">Format ICO/PNG/JPG, maksimal 512KB.</p>
                        @error('app_favicon')
                            <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div class="flex items-center gap-3">
                    <button type="submit"
                            class="bg-[#8E412E] text-white px-5 py-2 rounded-md hover:bg-[#BA6F4D] shadow">
                        <i class="bi bi-save2-fill"></i> Simpan Pengaturan
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
