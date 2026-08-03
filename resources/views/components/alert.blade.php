@props(['type' => 'info', 'message' => null, 'title' => null])

@php
    $icons = ['success' => 'fa-circle-check', 'warning' => 'fa-triangle-exclamation', 'danger' => 'fa-circle-exclamation', 'info' => 'fa-circle-info'];
@endphp
<div {{ $attributes->class(['sabira-alert', 'sabira-alert-'.$type]) }} role="alert">
    <i class="fas {{ $icons[$type] ?? $icons['info'] }}" aria-hidden="true"></i>
    <div>
        @if($title)<strong class="mb-0.5 block">{{ $title }}</strong>@endif
        {{ $message ?? $slot }}
    </div>
</div>
