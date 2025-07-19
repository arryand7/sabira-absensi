<x-app-layout>
    <div class="flex">
        <div class="mt-6 w-full sm:px-6 lg:px-8 space-y-6">
            <div class="bg-[#8D9382] shadow rounded-xl p-6 max-h-[calc(100vh-100px)] overflow-y-auto">
                <div class="max-w-xl">
                    @include('profile.partials.update-profile-information-form')
                </div>
            </div>

            <div class="bg-[#8D9382] shadow rounded-xl p-6 max-h-[calc(100vh-100px)] overflow-y-auto">
                <div class="max-w-xl">
                    @include('profile.partials.update-password-form')
                </div>
            </div>

        </div>
    </div>
</x-app-layout>
