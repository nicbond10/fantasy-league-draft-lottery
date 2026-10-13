@props(['basisPoints'])

@php($valid = (int) $basisPoints === 10000)

<div {{ $attributes->class(['flex items-center justify-between gap-4 rounded-xl border px-4 py-3', $valid ? 'border-gold/35 bg-gold/10' : 'border-line bg-raised']) }}>
    <span class="text-sm font-medium text-ink-secondary">{{ __('Lottery odds total') }}</span>
    <strong class="text-lg tabular-nums {{ $valid ? 'text-gold' : 'text-ink' }}">
        {{ \App\Support\Percentage::fromBasisPoints((int) $basisPoints) }}%
    </strong>
</div>
