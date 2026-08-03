@props(['title' => null, 'description' => null])
<section {{ $attributes->class('sabira-card') }}>
    @if($title || $description || isset($actions))
        <header class="sabira-card-header">
            <div>
                @if($title)<h2 class="sabira-card-title">{{ $title }}</h2>@endif
                @if($description)<p class="sabira-card-subtitle">{{ $description }}</p>@endif
            </div>
            @isset($actions)<div class="sabira-page-actions">{{ $actions }}</div>@endisset
        </header>
    @endif
    {{ $slot }}
</section>
