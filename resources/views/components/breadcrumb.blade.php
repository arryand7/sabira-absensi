@props(['title' => null, 'items' => []])

<nav aria-label="Breadcrumb" class="sabira-breadcrumb">
    @if(count($items))
        @foreach($items as $item)
            @unless($loop->first)<i class="fas fa-chevron-right" aria-hidden="true"></i>@endunless
            @if(!empty($item['route']) && !$loop->last)
                <a href="{{ route($item['route'], $item['parameters'] ?? []) }}">{{ $item['label'] }}</a>
            @else
                <span @if($loop->last) aria-current="page" @endif>{{ $item['label'] }}</span>
            @endif
        @endforeach
    @else
        <a href="{{ route('dashboard') }}">Beranda</a>
    @endif
    @if($title && !count($items))
        <i class="fas fa-chevron-right" aria-hidden="true"></i>
        <span aria-current="page">{{ $title }}</span>
    @endif
</nav>
