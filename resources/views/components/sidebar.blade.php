@props(['mobile' => false])

@php
    $user = auth()->user();
    $groups = collect(config('navigation', []))->map(function ($group) use ($user) {
        $items = collect($group['items'] ?? [])->filter(function ($item) use ($user) {
            return $user
                && in_array($user->role, $item['roles'] ?? [], true)
                && \Illuminate\Support\Facades\Route::has($item['route']);
        })->values();

        return array_merge($group, ['items' => $items]);
    })->filter(fn ($group) => $group['items']->isNotEmpty())->values();
@endphp

<aside class="sabira-sidebar {{ $mobile ? 'sabira-sidebar-mobile' : 'sabira-sidebar-desktop' }}" aria-label="Navigasi utama">
    <div class="sabira-brand">
        <a href="{{ route('dashboard') }}" class="sabira-brand-link" @if($mobile) @click="$dispatch('close-mobile-navigation')" @endif>
            <span class="sabira-brand-mark" aria-hidden="true"><i class="fas fa-graduation-cap"></i></span>
            <span>
                <strong>SABIRA ABSENSI</strong>
                <small>Monitoring Kehadiran dan Pembelajaran</small>
            </span>
        </a>
        @if($mobile)
            <button type="button" class="sabira-icon-button" aria-label="Tutup navigasi" @click="$dispatch('close-mobile-navigation')">
                <i class="fas fa-xmark" aria-hidden="true"></i>
            </button>
        @endif
    </div>

    <div class="sabira-sidebar-context">
        <span class="sabira-role-badge">{{ str_replace('_', ' ', ucfirst($user?->role ?? 'User')) }}</span>
        <span>{{ $activeYear?->name ?? 'Tahun ajaran belum aktif' }}</span>
    </div>

    <nav class="sabira-navigation">
        @foreach($groups as $group)
            @php
                $groupActive = $group['items']->contains(function ($item) {
                    return collect($item['active'] ?? [$item['route']])->contains(fn ($pattern) => request()->routeIs($pattern));
                });
            @endphp
            <section x-data="{ open: {{ $groupActive || $group['label'] === 'Beranda' ? 'true' : 'false' }} }" class="sabira-nav-group">
                <button type="button" class="sabira-nav-group-trigger" @click="open = !open" :aria-expanded="open">
                    <span>{{ $group['label'] }}</span>
                    <i class="fas fa-chevron-down" :class="open ? 'rotate-180' : ''" aria-hidden="true"></i>
                </button>
                <div x-show="open" x-collapse class="sabira-nav-items">
                    @foreach($group['items'] as $item)
                        @php
                            $active = collect($item['active'] ?? [$item['route']])->contains(fn ($pattern) => request()->routeIs($pattern));
                            $badge = match ($item['badge'] ?? null) {
                                'schedule_conflicts' => \Illuminate\Support\Facades\Schema::hasTable('schedule_conflicts')
                                    ? \App\Models\ScheduleConflict::pending()->count()
                                    : null,
                                default => $item['badge'] ?? null,
                            };
                        @endphp
                        <a
                            href="{{ route($item['route']) }}"
                            class="sabira-nav-link {{ $active ? 'is-active' : '' }}"
                            @if($active) aria-current="page" @endif
                            @if($mobile) @click="$dispatch('close-mobile-navigation')" @endif
                        >
                            <i class="{{ $item['icon'] }}" aria-hidden="true"></i>
                            <span>{{ $item['label'] }}</span>
                            @if(!empty($badge))<span class="sabira-nav-badge">{{ $badge }}</span>@endif
                        </a>
                    @endforeach
                </div>
            </section>
        @endforeach
    </nav>

    <div class="sabira-sidebar-user">
        <x-user-avatar :user="$user" size="sm" />
        <span>
            <strong>{{ $user?->name ?? 'Pengguna' }}</strong>
            <small>{{ $user?->email }}</small>
        </span>
    </div>
</aside>
