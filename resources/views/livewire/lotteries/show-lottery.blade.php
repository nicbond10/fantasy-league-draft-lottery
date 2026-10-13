<div class="mx-auto max-w-6xl space-y-8" x-data="lotteryPresentation">
    <header class="flex flex-col gap-5 sm:flex-row sm:items-start sm:justify-between">
        <div>
            <div class="mb-3 flex flex-wrap gap-2">
                <x-lottery-mode-badge :mode="$lottery->mode" />
                <x-lottery-status-badge :status="$lottery->status" />
            </div>
            <h1 class="text-3xl font-semibold tracking-tight text-ink sm:text-4xl">{{ $lottery->name }}</h1>
            <p class="mt-2 text-ink-secondary">
                {{ $lottery->mode === \App\Enums\LotteryMode::Official ? __('A definitive draft lottery with a saved final result.') : __('A repeatable, non-authoritative lottery simulation.') }}
            </p>
        </div>

        <div class="flex flex-wrap gap-3">
            @if ($lottery->isEditable())
                <flux:button :href="route('lotteries.edit', ['lotteryId' => $lottery->id])" wire:navigate>{{ __('Edit Lottery') }}</flux:button>
            @endif

            @if ($lottery->status !== \App\Enums\LotteryStatus::Archived)
                <flux:button
                    type="button"
                    variant="danger"
                    wire:click="archive"
                    wire:confirm="{{ __('Archive this lottery? It will become read-only and cannot be restored in this MVP.') }}"
                >{{ __('Archive') }}</flux:button>
            @endif
        </div>
    </header>

    @if ($lottery->status === \App\Enums\LotteryStatus::Archived)
        <div class="rounded-xl border border-line bg-raised px-4 py-3 text-sm text-ink-secondary" role="status">
            <strong class="text-ink">{{ __('Archived record.') }}</strong>
            {{ __('This lottery is read-only. Any saved official final order remains available below.') }}
        </div>
    @endif

    <div class="grid gap-6 lg:grid-cols-[minmax(0,1fr)_20rem]">
        <section class="app-card overflow-hidden" aria-labelledby="initial-order-heading">
            <header class="border-b border-line bg-raised px-4 py-4 sm:px-6">
                <p class="eyebrow">{{ __('Configuration') }}</p>
                <h2 id="initial-order-heading" class="mt-1 text-lg font-semibold text-ink">{{ __('Initial draft order') }}</h2>
            </header>
            <div class="overflow-x-auto">
                <table class="w-full min-w-[36rem] text-left text-sm">
                    <thead class="text-ink-muted">
                        <tr>
                            <th class="px-4 py-3 font-medium sm:px-6" scope="col">{{ __('Position') }}</th>
                            <th class="px-4 py-3 font-medium" scope="col">{{ __('Team') }}</th>
                            <th class="px-4 py-3 font-medium" scope="col">{{ __('Abbreviation') }}</th>
                            <th class="px-4 py-3 text-right font-medium sm:px-6" scope="col">{{ __('Lottery odds') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-line">
                        @forelse ($lottery->teams as $team)
                            <tr>
                                <td class="px-4 py-3 font-semibold text-ink-secondary sm:px-6">{{ $team->display_order }}</td>
                                <td class="px-4 py-3 font-medium text-ink">{{ $team->name }}</td>
                                <td class="px-4 py-3 text-ink-muted">{{ $team->abbreviation ?? '—' }}</td>
                                <td class="px-4 py-3 text-right tabular-nums text-ink-secondary sm:px-6">{{ \App\Support\Percentage::fromBasisPoints($team->weight) }}%</td>
                            </tr>
                        @empty
                            <tr><td class="px-4 py-10 text-center text-ink-muted" colspan="4">{{ __('No teams configured.') }}</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>

        <aside class="space-y-4" aria-label="{{ __('Lottery summary') }}">
            <div class="app-card p-5">
                <p class="eyebrow">{{ __('Format') }}</p>
                <p class="mt-2 font-semibold text-ink">{{ __('Weighted single pick') }}</p>
                <p class="mt-2 text-sm leading-6 text-ink-secondary">{{ __('One weighted winner moves to position #1. Every other team retains its relative order.') }}</p>
                <p class="mt-3 border-t border-line pt-3 text-sm text-ink-secondary">{{ __('Official reveal: :mode', ['mode' => ucfirst($lottery->presentation_mode->value)]) }}</p>
            </div>
            <x-odds-total :basis-points="$lottery->teams->sum('weight')" />
            <div class="app-card p-5">
                <p class="text-sm text-ink-secondary">{{ trans_choice(':count team|:count teams', $lottery->teams->count(), ['count' => $lottery->teams->count()]) }}</p>
                <p class="mt-1 text-sm text-ink-muted">{{ __('Updated :date', ['date' => $lottery->updated_at->format('M j, Y')]) }}</p>
            </div>
        </aside>
    </div>

    <section class="app-card p-5 sm:p-6" aria-labelledby="execution-heading">
        <div class="max-w-3xl">
            <p class="eyebrow">{{ __('Next step') }}</p>
            <h2 id="execution-heading" class="mt-1 text-xl font-semibold text-ink">{{ __('Lottery execution') }}</h2>

            @error('execution')
                <p class="mt-4 rounded-xl border border-danger/35 bg-danger/10 px-4 py-3 text-sm text-rose-200" role="alert">{{ $message }}</p>
            @enderror

            <div class="mt-4">
                @if ($lottery->status === \App\Enums\LotteryStatus::Ready && $lottery->mode === \App\Enums\LotteryMode::Official)
                    <p class="mb-5 leading-7 text-ink-secondary">{{ __('This official draw is irreversible. It runs once and saves the complete final draft order; the saved result can be replayed but never recalculated.') }}</p>
                    <button type="button" class="min-h-11 rounded-xl bg-gold px-5 text-sm font-semibold text-stone-950 hover:bg-gold-hover" x-on:click="openOfficialConfirmation">
                        {{ __('Run Official Lottery') }}
                    </button>
                @elseif ($lottery->status === \App\Enums\LotteryStatus::Ready && $lottery->mode === \App\Enums\LotteryMode::Simulation)
                    <p class="mb-5 leading-7 text-ink-secondary">{{ __('Simulation results are temporary and are not saved as authoritative draft results. Run it again whenever you want another possible outcome.') }}</p>
                    <button type="button" class="min-h-11 rounded-xl bg-gold px-5 text-sm font-semibold text-stone-950 hover:bg-gold-hover disabled:opacity-60" wire:click="runSimulation" wire:loading.attr="disabled" wire:target="runSimulation">
                        <span wire:loading.remove wire:target="runSimulation">{{ $result === null ? __('Simulate Lottery') : __('Simulate Again') }}</span>
                        <span wire:loading wire:target="runSimulation">{{ __('Generating result…') }}</span>
                    </button>
                @elseif ($lottery->status === \App\Enums\LotteryStatus::Completed)
                    <p class="mb-5 leading-7 text-ink-secondary">{{ __('The authoritative final order is saved. Replay uses this exact result and never performs another draw.') }}</p>
                    <flux:button type="button" wire:click="replay" wire:loading.attr="disabled" wire:target="replay">{{ __('Replay Presentation') }}</flux:button>
                @elseif ($lottery->status === \App\Enums\LotteryStatus::Draft)
                    <p class="leading-7 text-ink-secondary">{{ __('Complete the team configuration and mark this lottery ready before running it.') }}</p>
                @else
                    <p class="leading-7 text-ink-secondary">{{ __('Execution and active replay are disabled for this archived lottery.') }}</p>
                @endif
            </div>
        </div>
    </section>

    @if ($lottery->mode === \App\Enums\LotteryMode::Simulation)
        <section class="app-card overflow-hidden" x-show="simulationCount > 0" x-cloak aria-labelledby="simulation-statistics-heading">
            <header class="flex flex-col gap-4 border-b border-line bg-raised px-4 py-4 sm:flex-row sm:items-start sm:justify-between sm:px-6">
                <div>
                    <p class="eyebrow">{{ __('Current page session') }}</p>
                    <h2 id="simulation-statistics-heading" class="mt-1 text-lg font-semibold text-ink">{{ __('Temporary simulation statistics') }}</h2>
                    <p class="mt-1 text-sm leading-6 text-ink-secondary">{{ __('Temporary simulation results from this page session. These counts are not saved, and small samples may differ substantially from configured odds.') }}</p>
                    <p class="mt-2 text-sm font-semibold text-gold"><span x-text="simulationCount">0</span> <span x-text="simulationCount === 1 ? '{{ __('simulation run') }}' : '{{ __('simulations run') }}'"></span></p>
                </div>
                <button type="button" class="min-h-11 shrink-0 rounded-xl border border-line bg-surface px-4 text-sm font-semibold text-ink hover:border-ink-muted" x-on:click="resetSimulationStats">{{ __('Reset simulation stats') }}</button>
            </header>
            <div class="overflow-x-auto">
                <table class="w-full min-w-[38rem] text-left text-sm">
                    <thead class="text-ink-muted">
                        <tr>
                            <th class="px-4 py-3 font-medium sm:px-6" scope="col">{{ __('Team') }}</th>
                            <th class="px-4 py-3 text-right font-medium" scope="col">{{ __('Configured odds') }}</th>
                            <th class="px-4 py-3 text-right font-medium" scope="col">{{ __('Simulation wins') }}</th>
                            <th class="px-4 py-3 text-right font-medium sm:px-6" scope="col">{{ __('Observed win rate') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-line">
                        @foreach ($lottery->teams as $team)
                            <tr wire:key="simulation-stat-team-{{ $team->id }}">
                                <th class="px-4 py-3 font-semibold text-ink sm:px-6" scope="row">
                                    {{ $team->name }}
                                    @if ($team->abbreviation)<span class="ms-1 font-normal text-ink-muted">({{ $team->abbreviation }})</span>@endif
                                </th>
                                <td class="px-4 py-3 text-right tabular-nums text-ink-secondary">{{ \App\Support\Percentage::fromBasisPoints($team->weight) }}%</td>
                                <td class="px-4 py-3 text-right font-semibold tabular-nums text-ink" x-text="teamWins({{ $team->id }})">0</td>
                                <td class="px-4 py-3 text-right tabular-nums text-ink-secondary sm:px-6" x-text="observedRate({{ $team->id }})">0.00%</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <p class="sr-only" aria-live="polite" aria-atomic="true" x-text="statisticsMessage"></p>
        </section>
    @endif

    @if ($result !== null)
        @php
            $completedAt = $result['completed_at'] ? \Illuminate\Support\Carbon::parse($result['completed_at']) : null;
            $resultDescription = $result['official']
                ? __('Saved official result. This order is authoritative and is always rendered from its persisted snapshot.')
                : __('Latest transient simulation result. This result has not been saved as an official result.');
        @endphp

        @if ($result['official'])
            <div class="app-card grid gap-4 p-5 text-sm sm:grid-cols-2 sm:p-6">
                <div>
                    <p class="eyebrow">{{ __('Completed') }}</p>
                    <p class="mt-2 font-semibold text-ink">{{ $completedAt?->format('M j, Y \a\t g:i A') ?? __('Saved result') }}</p>
                </div>
                <div>
                    <p class="eyebrow">{{ __('Initiated by') }}</p>
                    <p class="mt-2 font-semibold text-ink">{{ $result['initiated_by']['name'] ?? __('Unavailable') }}</p>
                </div>
            </div>
        @endif

        <x-draft-order-list :positions="$result['positions']" :description="$resultDescription" aria-live="polite" />
    @endif

    @if ($lottery->status === \App\Enums\LotteryStatus::Ready && $lottery->mode === \App\Enums\LotteryMode::Official)
        <dialog x-ref="officialConfirmation" class="confirmation-dialog m-auto w-[min(92vw,32rem)] rounded-2xl border border-line bg-surface p-0 text-ink shadow-2xl" x-on:cancel.prevent="$refs.officialConfirmation.close()" aria-labelledby="official-confirmation-title" aria-describedby="official-confirmation-description">
            <div class="p-6">
                <p class="eyebrow">{{ __('Irreversible action') }}</p>
                <h2 id="official-confirmation-title" class="mt-2 text-xl font-semibold text-ink">{{ __('Run the official lottery?') }}</h2>
                <p id="official-confirmation-description" class="mt-3 leading-7 text-ink-secondary">{{ __('The weighted draw will run once. Its complete final draft order will be saved as authoritative and cannot be recalculated.') }}</p>
                <div class="mt-6 flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">
                    <button x-ref="cancelOfficial" type="button" class="min-h-11 rounded-xl border border-line bg-raised px-4 text-sm font-semibold text-ink hover:border-ink-muted" x-on:click="$refs.officialConfirmation.close()">{{ __('Cancel') }}</button>
                    <button type="button" class="min-h-11 rounded-xl bg-gold px-4 text-sm font-semibold text-stone-950 hover:bg-gold-hover" x-on:click="confirmOfficial">{{ __('Run Official Lottery') }}</button>
                </div>
            </div>
        </dialog>
    @endif

    <x-lottery-presentation />
</div>
