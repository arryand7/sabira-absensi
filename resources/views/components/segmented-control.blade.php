@props([
    'name' => 'status',
    'value' => 'Hadir',
    'options' => [
        'Hadir' => ['label' => 'Hadir', 'color' => 'peer-checked:bg-emerald-600 peer-checked:text-white', 'icon' => 'fa-check'],
        'Sakit' => ['label' => 'Sakit', 'color' => 'peer-checked:bg-amber-500 peer-checked:text-white', 'icon' => 'fa-notes-medical'],
        'Izin' => ['label' => 'Izin', 'color' => 'peer-checked:bg-[var(--sabira-primary)] peer-checked:text-white', 'icon' => 'fa-envelope-open-text'],
        'Alpa' => ['label' => 'Alpa', 'color' => 'peer-checked:bg-rose-600 peer-checked:text-white', 'icon' => 'fa-user-slash'],
    ],
])

<div class="grid grid-cols-4 gap-1 rounded-xl bg-slate-100 dark:bg-slate-800 p-1">
    @foreach($options as $val => $opt)
        <label class="relative cursor-pointer">
            <input type="radio" name="{{ $name }}" value="{{ $val }}" {{ strtolower($value) == strtolower($val) ? 'checked' : '' }} class="peer sr-only" />
            <div class="flex flex-col md:flex-row items-center justify-center gap-1 rounded-lg py-2.5 px-2 text-xs font-semibold text-slate-600 dark:text-slate-400 transition-all hover:bg-slate-200/60 dark:hover:bg-slate-700/60 {{ $opt['color'] }} shadow-sm min-h-[44px]">
                <i class="fas {{ $opt['icon'] }} text-xs"></i>
                <span>{{ $opt['label'] }}</span>
            </div>
        </label>
    @endforeach
</div>
