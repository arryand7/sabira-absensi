@props(['value' => 0, 'max' => 100, 'label' => 'Progres'])
@php($percentage = $max > 0 ? min(100, max(0, ($value / $max) * 100)) : 0)
<div {{ $attributes->class('sabira-progress') }}>
    <div class="sabira-progress-label"><span>{{ $label }}</span><span>{{ round($percentage) }}%</span></div>
    <div class="sabira-progress-track" role="progressbar" aria-label="{{ $label }}" aria-valuemin="0" aria-valuemax="100" aria-valuenow="{{ round($percentage) }}">
        <span style="width: {{ $percentage }}%"></span>
    </div>
</div>
