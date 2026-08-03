@props(['name'])
<select id="{{ $attributes->get('id', $name) }}" name="{{ $name }}" {{ $attributes->except('id')->class('sabira-select') }}>{{ $slot }}</select>
