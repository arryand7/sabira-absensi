<div
    x-data="{ mode: document.documentElement.dataset.themeMode || 'system' }"
    @sabira-theme-changed.window="mode = $event.detail.mode"
    class="sabira-theme-switcher"
    role="group"
    aria-label="Pilih tema tampilan"
>
    @foreach(['system' => ['fas fa-desktop', 'System'], 'light' => ['far fa-sun', 'Light'], 'dark' => ['far fa-moon', 'Dark']] as $mode => [$icon, $label])
        <button
            type="button"
            class="sabira-theme-option"
            :class="mode === '{{ $mode }}' ? 'is-active' : ''"
            :aria-pressed="mode === '{{ $mode }}'"
            aria-label="Gunakan tema {{ $label }}"
            title="Tema {{ $label }}"
            @click="window.SabiraTheme.set('{{ $mode }}')"
        >
            <i class="{{ $icon }}" aria-hidden="true"></i>
            <span class="hidden xl:inline">{{ $label }}</span>
        </button>
    @endforeach
</div>
