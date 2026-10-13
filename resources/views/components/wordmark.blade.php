@props(['compact' => false])

<span {{ $attributes->class(['inline-flex items-center gap-3']) }} aria-label="{{ __('Fantasy Draft Lottery') }}">
    <span class="relative grid size-9 shrink-0 place-items-center overflow-hidden rounded-lg bg-raised" aria-hidden="true">
        <span class="absolute inset-x-0 bottom-0 h-1 bg-gold"></span>
        <span class="h-4 w-4 rotate-45 rounded-sm border-2 border-brand-bright"></span>
    </span>
    @unless ($compact)
        <span class="grid leading-none">
            <span class="text-[0.68rem] font-semibold tracking-[0.18em] text-ink-secondary uppercase">{{ __('Fantasy Draft') }}</span>
            <span class="mt-1 text-base font-semibold tracking-tight text-ink">{{ __('Lottery') }}</span>
        </span>
    @endunless
</span>
