@props([
    'title' => 'Tidak Ada Data',
    'description' => 'Belum ada informasi yang tersedia untuk ditampilkan.',
    'icon' => 'fas fa-folder-open',
    'actionUrl' => null,
    'actionText' => null,
])

<div class="sabira-empty-state">
    <div class="sabira-empty-state-icon">
        <i class="{{ $icon }} text-2xl"></i>
    </div>
    <h4>{{ $title }}</h4>
    <p>{{ $description }}</p>
    @if($actionUrl && $actionText)
        <a href="{{ $actionUrl }}" class="sabira-button sabira-button-primary mt-4">
            <i class="fas fa-plus"></i> {{ $actionText }}
        </a>
    @endif
</div>
