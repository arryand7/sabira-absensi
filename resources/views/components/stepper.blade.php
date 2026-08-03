@props([
    'steps' => [
        1 => 'Validasi Jadwal',
        2 => 'Jurnal Pembelajaran',
        3 => 'Absensi Siswa',
        4 => 'Lokasi & Review',
        5 => 'Selesai',
    ],
    'currentStep' => 1,
    'currentStepExpression' => null,
])

@php
    $stepExpression = $currentStepExpression ?: (string) ((int) $currentStep);
@endphp

<div class="w-full py-4">
    <div class="flex items-center justify-between relative">
        <div class="absolute left-0 top-1/2 h-0.5 w-full bg-slate-200 dark:bg-slate-800 -translate-y-1/2 z-0"></div>
        
        @foreach($steps as $stepNum => $stepName)
            <div class="relative z-10 flex flex-col items-center group">
                <div
                    class="flex h-10 w-10 items-center justify-center rounded-full border-2 font-bold transition-all"
                    :class="{
                        'bg-[var(--sabira-primary)] border-[var(--sabira-primary)] text-white': {{ $stepExpression }} > {{ $stepNum }},
                        'bg-[var(--sabira-surface)] border-[var(--sabira-primary)] text-[var(--sabira-primary)] ring-4 ring-[var(--sabira-primary-disabled)]': {{ $stepExpression }} === {{ $stepNum }},
                        'bg-slate-100 dark:bg-slate-800 border-slate-300 dark:border-slate-700 text-slate-400': {{ $stepExpression }} < {{ $stepNum }}
                    }"
                >
                    <i x-show="{{ $stepExpression }} > {{ $stepNum }}" class="fas fa-check text-sm"></i>
                    <span x-show="{{ $stepExpression }} <= {{ $stepNum }}">{{ $stepNum }}</span>
                </div>
                <span
                    class="mt-2 text-xs font-semibold text-center hidden md:block"
                    :class="{
                        'text-[var(--sabira-primary)] font-bold': {{ $stepExpression }} === {{ $stepNum }},
                        'text-slate-700 dark:text-slate-300': {{ $stepExpression }} > {{ $stepNum }},
                        'text-slate-400': {{ $stepExpression }} < {{ $stepNum }}
                    }"
                >
                    {{ $stepName }}
                </span>
            </div>
        @endforeach
    </div>
</div>
