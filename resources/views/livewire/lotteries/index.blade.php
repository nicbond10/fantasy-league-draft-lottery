<div class="mx-auto max-w-7xl space-y-7">
    <header class="flex flex-col gap-5 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <p class="eyebrow">{{ __('Draft management') }}</p>
            <h1 class="mt-2 text-3xl font-semibold tracking-tight text-ink">{{ __('My lotteries') }}</h1>
            <p class="mt-2 text-ink-secondary">{{ __('Create and manage your private fantasy draft lotteries.') }}</p>
        </div>
        <flux:button :href="route('lotteries.create')" variant="primary" icon="plus" wire:navigate>{{ __('New Lottery') }}</flux:button>
    </header>

    <div class="flex flex-wrap items-center justify-between gap-4">
        <flux:radio.group wire:model.live="filter" variant="segmented" :label="__('Filter lotteries')">
            <flux:radio value="active">{{ __('Active') }}</flux:radio>
            <flux:radio value="archived">{{ __('Archived') }}</flux:radio>
        </flux:radio.group>
        <span class="text-sm text-ink-muted">{{ trans_choice(':count lottery|:count lotteries', $lotteries->total(), ['count' => $lotteries->total()]) }}</span>
    </div>

    @forelse ($lotteries as $lottery)
        @if ($loop->first)<div class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">@endif
            <article class="app-card flex min-h-56 flex-col p-5" wire:key="lottery-{{ $lottery->id }}">
                <div class="flex items-start justify-between gap-3">
                    <div class="flex flex-wrap gap-2">
                        <x-lottery-mode-badge :mode="$lottery->mode" />
                        <x-lottery-status-badge :status="$lottery->status" />
                    </div>
                    <span class="text-xs text-ink-muted">{{ $lottery->updated_at->format('M j, Y') }}</span>
                </div>
                <h2 class="mt-5 text-xl font-semibold tracking-tight text-ink">{{ $lottery->name }}</h2>
                <p class="mt-2 text-sm text-ink-secondary">{{ trans_choice(':count team configured|:count teams configured', $lottery->teams_count, ['count' => $lottery->teams_count]) }}</p>
                <div class="mt-auto pt-6">
                    <flux:button class="w-full" :href="route('lotteries.show', ['lotteryId' => $lottery->id])" wire:navigate>
                        {{ $lottery->status === \App\Enums\LotteryStatus::Completed ? __('View final order') : __('View lottery') }}
                    </flux:button>
                </div>
            </article>
        @if ($loop->last)</div>@endif
    @empty
        <x-empty-state
            :title="$filter === 'archived' ? __('No archived lotteries') : __('No active lotteries')"
            :description="$filter === 'archived' ? __('Lotteries you archive will remain available here as read-only records.') : __('Create a lottery to configure teams, odds, and the initial draft order.')"
        >
            @if ($filter !== 'archived')
                <flux:button :href="route('lotteries.create')" variant="primary" wire:navigate>{{ __('Create lottery') }}</flux:button>
            @endif
        </x-empty-state>
    @endforelse

    <div>{{ $lotteries->links() }}</div>
</div>
