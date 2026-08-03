@props(['label' => 'Tabel data'])
<div class="sabira-table-wrap">
    <table aria-label="{{ $label }}" {{ $attributes->class('sabira-data-table') }}>{{ $slot }}</table>
</div>
