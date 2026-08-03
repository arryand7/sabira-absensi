@props([
    'status' => 'draft',
    'size' => 'normal', // sm, normal, lg
])

@php
    $statusMap = [
        'aktif' => ['label' => 'Aktif', 'color' => 'bg-emerald-100 text-emerald-800 dark:bg-emerald-950 dark:text-emerald-300 border-emerald-200 dark:border-emerald-800', 'icon' => 'fa-check-circle'],
        'active' => ['label' => 'Aktif', 'color' => 'bg-emerald-100 text-emerald-800 dark:bg-emerald-950 dark:text-emerald-300 border-emerald-200 dark:border-emerald-800', 'icon' => 'fa-check-circle'],
        'selesai' => ['label' => 'Selesai', 'color' => 'bg-emerald-100 text-emerald-800 dark:bg-emerald-950 dark:text-emerald-300 border-emerald-200 dark:border-emerald-800', 'icon' => 'fa-check-double'],
        'completed' => ['label' => 'Selesai', 'color' => 'bg-emerald-100 text-emerald-800 dark:bg-emerald-950 dark:text-emerald-300 border-emerald-200 dark:border-emerald-800', 'icon' => 'fa-check-double'],
        'inside_geofence' => ['label' => 'Di Dalam Area', 'color' => 'bg-emerald-100 text-emerald-800 dark:bg-emerald-950 dark:text-emerald-300 border-emerald-200 dark:border-emerald-800', 'icon' => 'fa-map-marker-alt'],
        'draft' => ['label' => 'Draft', 'color' => 'bg-amber-100 text-amber-800 dark:bg-amber-950 dark:text-amber-300 border-amber-200 dark:border-amber-800', 'icon' => 'fa-pencil-alt'],
        'belum_dilaporkan' => ['label' => 'Belum Dilaporkan', 'color' => 'bg-amber-100 text-amber-800 dark:bg-amber-950 dark:text-amber-300 border-amber-200 dark:border-amber-800', 'icon' => 'fa-clock'],
        'outside_geofence' => ['label' => 'Di Luar Area', 'color' => 'bg-rose-100 text-rose-800 dark:bg-rose-950 dark:text-rose-300 border-rose-200 dark:border-rose-800', 'icon' => 'fa-exclamation-triangle'],
        'bermasalah' => ['label' => 'Bermasalah', 'color' => 'bg-rose-100 text-rose-800 dark:bg-rose-950 dark:text-rose-300 border-rose-200 dark:border-rose-800', 'icon' => 'fa-exclamation-circle'],
        'perlu_ditinjau' => ['label' => 'Perlu Ditinjau', 'color' => 'bg-indigo-100 text-indigo-800 dark:bg-indigo-950 dark:text-indigo-300 border-indigo-200 dark:border-indigo-800', 'icon' => 'fa-eye'],
        'conflict' => ['label' => 'Konflik (Manual)', 'color' => 'bg-indigo-100 text-indigo-800 dark:bg-indigo-950 dark:text-indigo-300 border-indigo-200 dark:border-indigo-800', 'icon' => 'fa-random'],
        'dibatalkan' => ['label' => 'Dibatalkan', 'color' => 'bg-slate-100 text-slate-700 dark:bg-slate-800 dark:text-slate-300 border-slate-200 dark:border-slate-700', 'icon' => 'fa-times-circle'],
        'ditangguhkan' => ['label' => 'Ditangguhkan', 'color' => 'bg-rose-100 text-rose-800 dark:bg-rose-950 dark:text-rose-300 border-rose-200 dark:border-rose-800', 'icon' => 'fa-ban'],
        'suspended' => ['label' => 'Ditangguhkan', 'color' => 'bg-rose-100 text-rose-800 dark:bg-rose-950 dark:text-rose-300 border-rose-200 dark:border-rose-800', 'icon' => 'fa-ban'],
        'substitute' => ['label' => 'Guru Pengganti', 'color' => 'bg-blue-100 text-blue-800 dark:bg-blue-950 dark:text-blue-300 border-blue-200 dark:border-blue-800', 'icon' => 'fa-user-tag'],
    ];

    $badge = $statusMap[strtolower($status)] ?? ['label' => ucfirst($status), 'color' => 'bg-slate-100 text-slate-800 border-slate-200', 'icon' => 'fa-info-circle'];

    $padding = match($size) {
        'sm' => 'px-2 py-0.5 text-[11px]',
        'lg' => 'px-3 py-1.5 text-sm',
        default => 'px-2.5 py-1 text-xs',
    };
@endphp

<span class="inline-flex items-center gap-1.5 font-semibold rounded-full border {{ $badge['color'] }} {{ $padding }}">
    <i class="fas {{ $badge['icon'] }} text-[10px]"></i>
    <span>{{ $badge['label'] }}</span>
</span>
