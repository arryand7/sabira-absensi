<x-app-layout>
    <x-slot name="sidebar">
        <x-admin-sidenav />
    </x-slot>

    <div class="w-full mt-6 sm:px-6 lg:px-8 space-y-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-xl font-semibold text-[#1C1E17]">Pengaturan SSO</h1>
                <p class="mt-1 text-sm text-[#5C644C]">Kelola koneksi OAuth2 ke Sabira Connect untuk login terpadu.</p>
            </div>
        </div>

        <form action="{{ route('admin.settings.sso.update') }}" method="POST" class="bg-[#EEF3E9] p-6 rounded-2xl shadow-md border border-[#D6D8D2] space-y-5">
            @csrf
            @method('PUT')

            <div class="grid gap-4 md:grid-cols-2">
                <div>
                    <label for="sso_base_url" class="block text-sm font-semibold text-[#5C644C]">Base URL SSO</label>
                    <input type="url" id="sso_base_url" name="sso_base_url" value="{{ old('sso_base_url', $setting->sso_base_url) }}" placeholder="https://gate.sabira-iibs.id"
                        class="mt-1 w-full rounded-lg border border-[#D6D8D2] px-3 py-2 text-sm focus:border-[#5C644C] focus:outline-none focus:ring-2 focus:ring-[#5C644C]/20">
                    @error('sso_base_url') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label for="sso_redirect_uri" class="block text-sm font-semibold text-[#5C644C]">Redirect URI</label>
                    <input type="url" id="sso_redirect_uri" name="sso_redirect_uri" value="{{ old('sso_redirect_uri', $setting->sso_redirect_uri) }}" placeholder="{{ route('sso.callback') }}"
                        class="mt-1 w-full rounded-lg border border-[#D6D8D2] px-3 py-2 text-sm focus:border-[#5C644C] focus:outline-none focus:ring-2 focus:ring-[#5C644C]/20">
                    <p class="mt-1 text-xs text-[#5C644C]/70">Gunakan endpoint callback aplikasi ini (contoh: {{ route('sso.callback') }}).</p>
                    @error('sso_redirect_uri') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>
            </div>

            <div class="grid gap-4 md:grid-cols-2">
                <div>
                    <label for="sso_client_id" class="block text-sm font-semibold text-[#5C644C]">Client ID</label>
                    <input type="text" id="sso_client_id" name="sso_client_id" value="{{ old('sso_client_id', $setting->sso_client_id) }}"
                        class="mt-1 w-full rounded-lg border border-[#D6D8D2] px-3 py-2 text-sm focus:border-[#5C644C] focus:outline-none focus:ring-2 focus:ring-[#5C644C]/20">
                    @error('sso_client_id') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label for="sso_client_secret" class="block text-sm font-semibold text-[#5C644C]">Client Secret</label>
                    <input type="password" id="sso_client_secret" name="sso_client_secret" placeholder="********"
                        class="mt-1 w-full rounded-lg border border-[#D6D8D2] px-3 py-2 text-sm focus:border-[#5C644C] focus:outline-none focus:ring-2 focus:ring-[#5C644C]/20">
                    <p class="mt-1 text-xs text-[#5C644C]/70">Kosongkan jika tidak ingin mengubah secret.</p>
                    @error('sso_client_secret') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>
            </div>

            <div>
                <label for="sso_scopes" class="block text-sm font-semibold text-[#5C644C]">Scopes</label>
                <input type="text" id="sso_scopes" name="sso_scopes" value="{{ old('sso_scopes', $setting->sso_scopes) }}" placeholder="openid profile email roles"
                    class="mt-1 w-full rounded-lg border border-[#D6D8D2] px-3 py-2 text-sm focus:border-[#5C644C] focus:outline-none focus:ring-2 focus:ring-[#5C644C]/20">
                @error('sso_scopes') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>

            <div class="flex justify-end gap-2">
                <button type="reset" class="px-4 py-2 bg-gray-200 text-sm rounded-lg">Reset</button>
                <button type="submit" class="px-4 py-2 bg-[#5C644C] text-white text-sm rounded-lg">Simpan</button>
            </div>
        </form>
    </div>
</x-app-layout>
