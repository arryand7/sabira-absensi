@props(['startName' => 'start_date', 'endName' => 'end_date', 'start' => null, 'end' => null])
<div {{ $attributes->class('sabira-date-range') }}>
    <x-input :name="$startName" type="date" :value="$start" aria-label="Tanggal mulai" />
    <span aria-hidden="true">—</span>
    <x-input :name="$endName" type="date" :value="$end" aria-label="Tanggal akhir" />
</div>
