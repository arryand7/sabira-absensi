@props(['label', 'href' => null, 'type' => 'button'])
@if($href)
    <a href="{{ $href }}" aria-label="{{ $label }}" title="{{ $label }}" {{ $attributes->class('sabira-icon-button') }}>{{ $slot }}</a>
@else
    <button type="{{ $type }}" aria-label="{{ $label }}" title="{{ $label }}" {{ $attributes->class('sabira-icon-button') }}>{{ $slot }}</button>
@endif
