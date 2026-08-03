@props([
    'title' => '',
    'value' => '0',
    'subtitle' => null,
    'icon' => 'fas fa-chart-line',
    'color' => 'info',
    'trend' => null,
    'trendUp' => true,
    'link' => null,
    'linkText' => 'Lihat Detail',
])

@php
    $semanticClass = match($color) {
        'emerald', 'success' => 'is-success',
        'amber', 'warning' => 'is-warning',
        'rose', 'danger' => 'is-danger',
        default => 'is-info',
    };
@endphp

<div class="sabira-stat-card {{ $semanticClass }}">
    <div class="flex items-center justify-between">
        <div>
            <p class="sabira-stat-label">{{ $title }}</p>
            <h3 class="sabira-stat-value">{{ $value }}</h3>
            @if($subtitle)
                <p class="sabira-stat-subtitle">{{ $subtitle }}</p>
            @endif
            @if($trend !== null)
                <div class="mt-2 flex items-center text-xs font-medium {{ $trendUp ? 'text-emerald-600 dark:text-emerald-400' : 'text-rose-600 dark:text-rose-400' }}">
                    <i class="fas {{ $trendUp ? 'fa-arrow-up' : 'fa-arrow-down' }} mr-1"></i>
                    <span>{{ $trend }}</span>
                </div>
            @endif
        </div>
        <div class="sabira-stat-icon">
            <i class="{{ $icon }} text-xl"></i>
        </div>
    </div>
    @if($link)
        <div class="mt-4 border-t border-[var(--sabira-border-soft)] pt-3">
            <a href="{{ $link }}" class="sabira-inline-link">
                {{ $linkText }} <i class="fas fa-chevron-right ml-1 text-[10px]"></i>
            </a>
        </div>
    @endif
</div>
