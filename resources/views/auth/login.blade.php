<x-app-shell guest header-title="Masuk" header-subtitle="SABIRA ABSENSI">
    <section class="sabira-card w-full">
        <div class="mb-6">
            <h1 class="text-2xl font-semibold text-[var(--sabira-ink)]">Selamat datang</h1>
            <p class="mt-1 text-sm text-[var(--sabira-muted)]">Masuk untuk melanjutkan ke monitoring kehadiran dan pembelajaran.</p>
        </div>
    <!-- Session Status -->
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form method="POST" action="{{ route('login') }}" class="space-y-4">
        @csrf

        <!-- Email -->
        <div>
            <x-input-label for="email" :value="__('Email')" />
            <x-text-input id="email" type="email" name="email" :value="old('email')" required autofocus autocomplete="username" class="block w-full mt-1" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <!-- Password -->
        <div>
            <x-input-label for="password" :value="__('Password')" />
            <x-text-input id="password" type="password" name="password" required autocomplete="current-password" class="block w-full mt-1" />
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <!-- Remember Me + Button -->
        <div class="flex items-center justify-between">
            <label class="inline-flex items-center">
                <input type="checkbox" name="remember" class="rounded border-[var(--sabira-border)] text-[var(--sabira-primary)] focus:ring-[var(--sabira-primary)]">
                <span class="ms-2 text-sm text-[var(--sabira-muted)]">{{ __('Remember me') }}</span>
            </label>

            <x-primary-button>
                {{ __('Log in') }}
            </x-primary-button>
        </div>
    </form>

    <div class="my-4 flex items-center gap-3 text-xs text-gray-400">
        <span class="h-px flex-1 bg-gray-200"></span>
        <span>atau</span>
        <span class="h-px flex-1 bg-gray-200"></span>
    </div>

    <a href="{{ route('sso.login') }}" class="sabira-button sabira-button-secondary w-full">
        Masuk dengan Sabira Connect
    </a>
    </section>
</x-app-shell>
