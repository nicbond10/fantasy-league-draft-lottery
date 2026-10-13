@props([
    'positions',
    'heading' => 'Final draft order',
    'description' => null,
])

<section {{ $attributes->class(['app-card overflow-hidden']) }} aria-labelledby="final-order-heading">
    <header class="border-b border-line bg-raised px-4 py-4 sm:px-6">
        <h2 id="final-order-heading" class="text-lg font-semibold text-ink">{{ $heading }}</h2>
        @if ($description)
            <p class="mt-1 text-sm text-ink-secondary">{{ $description }}</p>
        @endif
    </header>
    <ol class="divide-y divide-line">
        @foreach ($positions as $position)
            <li class="flex items-center gap-4 px-4 py-4 sm:px-6 {{ $position['winner'] ? 'bg-gold/8' : '' }}">
                <span class="grid size-10 shrink-0 place-items-center rounded-xl font-bold tabular-nums {{ $position['winner'] ? 'bg-gold text-stone-950' : 'bg-raised text-ink-secondary' }}">
                    {{ $position['final_position'] }}
                </span>
                <span class="min-w-0 flex-1">
                    <span class="block truncate font-semibold text-ink">{{ $position['team_name'] }}</span>
                    @if ($position['winner'])
                        <span class="mt-0.5 block text-xs font-semibold tracking-wide text-gold uppercase">{{ __('First overall pick winner') }}</span>
                    @elseif (! empty($position['abbreviation']))
                        <span class="mt-0.5 block text-xs text-ink-muted">{{ $position['abbreviation'] }}</span>
                    @endif
                </span>
                <span class="text-sm tabular-nums text-ink-secondary">{{ $position['percentage'] }}%</span>
            </li>
        @endforeach
    </ol>
</section>
