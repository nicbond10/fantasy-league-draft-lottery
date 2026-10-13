@props(['status'])

@php
    $value = $status instanceof \App\Enums\LotteryStatus ? $status->value : (string) $status;
    $styles = match ($value) {
        'ready' => 'border-gold/35 bg-gold/10 text-gold',
        'completed' => 'border-success/35 bg-success/10 text-emerald-300',
        'archived' => 'border-line bg-raised text-ink-muted',
        default => 'border-brand/35 bg-brand/10 text-rose-200',
    };
@endphp

<span {{ $attributes->class(["inline-flex items-center rounded-full border px-2.5 py-1 text-xs font-semibold", $styles]) }}>
    {{ ucfirst($value) }}
</span>
