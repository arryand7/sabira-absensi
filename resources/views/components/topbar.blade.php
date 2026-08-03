@props(['title' => 'Sabira Absensi', 'subtitle' => null, 'actions' => null])

<header class="sabira-topbar">
    <div class="sabira-topbar-leading">
        <button type="button" class="sabira-icon-button lg:hidden" @click="mobileNavigationOpen = true" aria-label="Buka navigasi">
            <i class="fas fa-bars" aria-hidden="true"></i>
        </button>
        <div class="min-w-0">
            <x-breadcrumb :title="$title" />
            <h1 class="sabira-page-title truncate">{{ $title }}</h1>
            @if($subtitle)<p class="sabira-page-subtitle truncate">{{ $subtitle }}</p>@endif
        </div>
    </div>

    <div class="sabira-topbar-actions">
        @if($actions && trim((string) $actions) !== '')
            <div class="hidden md:flex items-center gap-2">{{ $actions }}</div>
        @endif
        <x-theme-switcher />

        <div x-data="{ open: false }" class="relative">
            <button type="button" class="sabira-user-menu-trigger" @click="open = !open" @keydown.escape.window="open = false" :aria-expanded="open" aria-haspopup="menu">
                <x-user-avatar :user="auth()->user()" size="sm" />
                <span class="hidden xl:block min-w-0 text-left">
                    <strong>{{ auth()->user()?->name }}</strong>
                    <small>{{ str_replace('_', ' ', ucfirst(auth()->user()?->role ?? 'User')) }}</small>
                </span>
                <i class="fas fa-chevron-down hidden sm:block" aria-hidden="true"></i>
            </button>
            <div x-show="open" x-cloak x-transition @click.outside="open = false" class="sabira-user-menu" role="menu">
                <a href="{{ route('profile.edit') }}" class="sabira-user-menu-item" role="menuitem"><i class="far fa-user"></i> Profil Saya</a>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="sabira-user-menu-item w-full" role="menuitem"><i class="fas fa-arrow-right-from-bracket"></i> Keluar</button>
                </form>
            </div>
        </div>
    </div>
</header>
