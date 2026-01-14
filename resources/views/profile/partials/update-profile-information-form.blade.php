<section>
    <header>
        <h2 class="font-semibold text-xl text-[#292D22]">
            {{ __('Profile Information') }}
        </h2>

        <p class="mt-1 text-sm text-gray-900 dark:text-gray-100">
            {{ __("Update your account's profile information and email address.") }}
        </p>
    </header>

    <form id="send-verification" method="post" action="{{ route('verification.send') }}">
        @csrf
    </form>

    <form method="post" action="{{ route('profile.update') }}" enctype="multipart/form-data" class="mt-6 space-y-6">
        @csrf
        @method('patch')

        <div>
            <x-input-label for="foto" :value="__('Foto Profil')" />
            @php
                $fotoUrl = $user?->karyawan?->foto
                    ? asset('storage/' . $user->karyawan->foto)
                    : asset('images/default-photo.jpg');
            @endphp
            <div class="mt-2 flex items-center gap-4">
                <img src="{{ $fotoUrl }}" alt="Foto Profil" class="h-16 w-16 rounded-full object-cover border border-gray-200">
                <input id="foto" name="foto" type="file" accept=".jpg,.jpeg,.png,.webp"
                       class="block w-full text-sm text-gray-700 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-[#8E412E] file:text-white hover:file:bg-[#BA6F4D]">
            </div>
            <x-input-error class="mt-2" :messages="$errors->get('foto')" />
        </div>

        <div>
            <x-input-label for="name" :value="__('Name')" />
            <x-text-input id="name" name="name" type="text" class="w-full rounded border-gray-300 bg-[#EEF3E9] text-[#1C1E17] @error('name') border-red-500 @enderror" :value="old('name', $user->name)" required autofocus autocomplete="name" />
            <x-input-error class="mt-2" :messages="$errors->get('name')" />
        </div>

        <div>
            <x-input-label for="email" :value="__('Email')" />
            <x-text-input id="email" name="email" type="email" class="w-full rounded border-gray-300 bg-[#EEF3E9] text-[#1C1E17] @error('name') border-red-500 @enderror" :value="old('email', $user->email)" required autocomplete="username" />
            <x-input-error class="mt-2" :messages="$errors->get('email')" />

            @if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! $user->hasVerifiedEmail())
                <div>
                    <p class="text-sm mt-2 text-gray-800 dark:text-gray-200">
                        {{ __('Your email address is unverified.') }}

                        <button form="send-verification" class="underline text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 dark:focus:ring-offset-gray-800">
                            {{ __('Click here to re-send the verification email.') }}
                        </button>
                    </p>

                    @if (session('status') === 'verification-link-sent')
                        <p class="mt-2 font-medium text-sm text-green-600 dark:text-green-400">
                            {{ __('A new verification link has been sent to your email address.') }}
                        </p>
                    @endif
                </div>
            @endif
        </div>

        <div>
            <x-input-label for="no_hp" :value="__('No HP')" />
            <x-text-input id="no_hp" name="no_hp" type="text" class="w-full rounded border-gray-300 bg-[#EEF3E9] text-[#1C1E17]" :value="old('no_hp', $user->karyawan->no_hp ?? '')" autocomplete="tel" />
            <x-input-error class="mt-2" :messages="$errors->get('no_hp')" />
        </div>

        <div>
            <x-input-label for="alamat" :value="__('Alamat')" />
            <textarea id="alamat" name="alamat" rows="3"
                class="w-full rounded border-gray-300 bg-[#EEF3E9] text-[#1C1E17]">{{ old('alamat', $user->karyawan->alamat ?? '') }}</textarea>
            <x-input-error class="mt-2" :messages="$errors->get('alamat')" />
        </div>

        <div class="flex items-center gap-4">
            <x-primary-button>{{ __('Save') }}</x-primary-button>

            @if (session('status') === 'profile-updated')
                <p
                    x-data="{ show: true }"
                    x-show="show"
                    x-transition
                    x-init="setTimeout(() => show = false, 2000)"
                    class="text-sm text-gray-600 dark:text-gray-400"
                >{{ __('Saved.') }}</p>
            @endif
        </div>
    </form>
</section>
