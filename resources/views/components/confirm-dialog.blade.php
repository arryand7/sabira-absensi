@props(['name', 'title' => 'Konfirmasi tindakan', 'description' => null, 'confirmText' => 'Lanjutkan', 'variant' => 'danger', 'action' => null, 'method' => 'POST'])
<x-modal :name="$name" max-width="md" focusable>
    <div class="p-6">
        <h2 class="sabira-card-title">{{ $title }}</h2>
        @if($description)<p class="sabira-card-subtitle mt-2">{{ $description }}</p>@endif
        <div class="mt-6 flex justify-end gap-3">
            <x-button variant="secondary" type="button" x-on:click="$dispatch('close-modal', '{{ $name }}')">Batal</x-button>
            @if($action)
                <form method="POST" action="{{ $action }}">
                    @csrf
                    @unless(strtoupper($method) === 'POST') @method($method) @endunless
                    <x-button :variant="$variant" type="submit">{{ $confirmText }}</x-button>
                </form>
            @else
                <x-button :variant="$variant" type="button" x-on:click="$dispatch('confirmed', '{{ $name }}')">{{ $confirmText }}</x-button>
            @endif
        </div>
    </div>
</x-modal>
