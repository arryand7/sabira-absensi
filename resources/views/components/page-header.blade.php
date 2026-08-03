@props(['title', 'description' => null])
<header {{ $attributes->class('sabira-content-header') }}>
    <div>
        <h1>{{ $title }}</h1>
        @if($description)<p>{{ $description }}</p>@endif
    </div>
    @isset($actions)<div class="sabira-page-actions">{{ $actions }}</div>@endisset
</header>
