@props(['label', 'name', 'hint' => null, 'required' => false])
<div {{ $attributes->class('sabira-form-field') }}>
    <label for="{{ $name }}">{{ $label }} @if($required)<span aria-hidden="true">*</span>@endif</label>
    {{ $slot }}
    @if($hint)<p class="sabira-field-hint">{{ $hint }}</p>@endif
    <x-input-error :messages="$errors->get($name)" />
</div>
