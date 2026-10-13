@props([
    'sidebar' => false,
])

@if($sidebar)
    <flux:sidebar.brand name="Fantasy Draft Lottery" {{ $attributes }}>
        <x-slot name="logo" class="flex items-center">
            <x-wordmark compact />
        </x-slot>
    </flux:sidebar.brand>
@else
    <flux:brand name="Fantasy Draft Lottery" {{ $attributes }}>
        <x-slot name="logo" class="flex items-center">
            <x-wordmark compact />
        </x-slot>
    </flux:brand>
@endif
