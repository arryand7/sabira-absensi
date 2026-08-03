@props(['variant' => 'primary', 'href' => null, 'type' => 'button'])

@php
    $classes = 'sabira-button sabira-button-'.$variant;
@endphp

@if($href)
    <a href="{{ $href }}" {{ $attributes->class($classes) }}>{{ $slot }}</a>
@else
    <button type="{{ $type }}" {{ $attributes->class($classes) }}>{{ $slot }}</button>
@endif
