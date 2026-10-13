@props(['title', 'description'])

<div {{ $attributes->class(['app-card border-dashed px-6 py-12 text-center']) }}>
    <div class="mx-auto mb-4 grid size-11 place-items-center rounded-full bg-raised text-gold" aria-hidden="true">
        <flux:icon.trophy class="size-5" />
    </div>
    <h2 class="text-base font-semibold text-ink">{{ $title }}</h2>
    <p class="mx-auto mt-2 max-w-md text-sm leading-6 text-ink-secondary">{{ $description }}</p>
    @if ($slot->isNotEmpty())
        <div class="mt-5">{{ $slot }}</div>
    @endif
</div>
