@props(['user' => null, 'size' => 'md'])

@php
    $photo = $user?->karyawan?->foto;
    $sizes = ['sm' => 'h-9 w-9 text-xs', 'md' => 'h-10 w-10 text-sm', 'lg' => 'h-14 w-14 text-base'];
@endphp

@if($photo)
    <img src="{{ asset('storage/'.$photo) }}" alt="Foto {{ $user->name }}" class="sabira-avatar {{ $sizes[$size] ?? $sizes['md'] }} object-cover">
@else
    <span class="sabira-avatar {{ $sizes[$size] ?? $sizes['md'] }}" aria-label="Avatar {{ $user?->name ?? 'Pengguna' }}">
        {{ mb_strtoupper(mb_substr($user?->name ?? 'U', 0, 1)) }}
    </span>
@endif
