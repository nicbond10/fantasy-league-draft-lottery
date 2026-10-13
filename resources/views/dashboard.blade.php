<div class="mx-auto max-w-7xl space-y-8">
    <header class="flex flex-col gap-5 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <p class="eyebrow">{{ __('League control room') }}</p>
            <h1 class="mt-2 text-3xl font-semibold tracking-tight text-ink sm:text-4xl">{{ __('Dashboard') }}</h1>
            <p class="mt-2 max-w-2xl text-ink-secondary">{{ __('Build, run, and revisit your fantasy draft lotteries from one place.') }}</p>
        </div>
        <flux:button :href="route('lotteries.create')" variant="primary" icon="plus" wire:navigate>{{ __('New Lottery') }}</flux:button>
    </header>

    <section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4" aria-label="{{ __('Lottery summary') }}">
        <article class="app-card p-5">
            <p class="text-sm font-medium text-ink-secondary">{{ __('Active lotteries') }}</p>
            <p class="mt-3 text-3xl font-semibold tabular-nums text-ink">{{ $activeCount }}</p>
        </article>
        <article class="app-card p-5">
            <p class="text-sm font-medium text-ink-secondary">{{ __('Ready to run') }}</p>
            <p class="mt-3 text-3xl font-semibold tabular-nums text-gold">{{ $readyCount }}</p>
        </article>
        <article class="app-card p-5">
            <p class="text-sm font-medium text-ink-secondary">{{ __('Completed official') }}</p>
            <p class="mt-3 text-3xl font-semibold tabular-nums text-gold">{{ $completedOfficialCount }}</p>
        </article>
        <article class="app-card p-5">
            <p class="text-sm font-medium text-ink-secondary">{{ __('Simulation lotteries') }}</p>
            <p class="mt-3 text-3xl font-semibold tabular-nums text-ink">{{ $simulationCount }}</p>
        </article>
    </section>

    <section aria-labelledby="recent-lotteries-heading">
        <div class="mb-4 flex items-center justify-between gap-4">
            <div>
                <p class="eyebrow">{{ __('Recent activity') }}</p>
                <h2 id="recent-lotteries-heading" class="mt-1 text-xl font-semibold text-ink">{{ __('Recent lotteries') }}</h2>
            </div>
            <a href="{{ route('lotteries.index') }}" class="text-sm font-semibold text-rose-300 hover:text-rose-200" wire:navigate>{{ __('View all') }}</a>
        </div>

        @forelse ($recentLotteries as $lottery)
            @if ($loop->first)<div class="app-card divide-y divide-line">@endif
                <a href="{{ route('lotteries.show', ['lotteryId' => $lottery->id]) }}" class="flex min-h-20 items-center gap-4 px-4 py-4 transition hover:bg-raised focus-visible:bg-raised sm:px-6" wire:navigate>
                    <span class="grid size-10 shrink-0 place-items-center rounded-xl bg-raised text-sm font-bold text-gold" aria-hidden="true">{{ str($lottery->name)->substr(0, 1)->upper() }}</span>
                    <span class="min-w-0 flex-1">
                        <span class="block truncate font-semibold text-ink">{{ $lottery->name }}</span>
                        <span class="mt-1 block text-sm text-ink-muted">{{ trans_choice(':count team|:count teams', $lottery->teams_count, ['count' => $lottery->teams_count]) }} · {{ __('Updated :date', ['date' => $lottery->updated_at->diffForHumans()]) }}</span>
                    </span>
                    <span class="hidden items-center gap-2 sm:flex">
                        <x-lottery-mode-badge :mode="$lottery->mode" />
                        <x-lottery-status-badge :status="$lottery->status" />
                    </span>
                    <flux:icon.chevron-right class="size-4 text-ink-muted" aria-hidden="true" />
                </a>
            @if ($loop->last)</div>@endif
        @empty
            <x-empty-state :title="__('No lotteries yet')" :description="__('Create your first official or simulation lottery to begin building a draft order.')">
                <flux:button :href="route('lotteries.create')" variant="primary" wire:navigate>{{ __('Create your first lottery') }}</flux:button>
            </x-empty-state>
        @endforelse
    </section>
</div>
