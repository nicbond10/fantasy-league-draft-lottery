@props(['mode'])

@php($value = $mode instanceof \App\Enums\LotteryMode ? $mode->value : (string) $mode)

<span {{ $attributes->class([
    'inline-flex items-center rounded-full border px-2.5 py-1 text-xs font-semibold',
    $value === 'official' ? 'border-brand/35 bg-brand/10 text-rose-200' : 'border-line bg-raised text-ink-secondary',
]) }}>
    {{ ucfirst($value) }}
</span>
