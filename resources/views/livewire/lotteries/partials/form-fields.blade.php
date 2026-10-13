<div class="space-y-8" x-data="lotteryOddsTotal" x-on:input="recalculate">
    <div class="app-card grid gap-6 p-5 lg:grid-cols-3 lg:p-6">
        <flux:input
            wire:model.blur="name"
            :label="__('Lottery name')"
            type="text"
            maxlength="120"
            required
            autofocus
        />

        <flux:radio.group wire:model="mode" :label="__('Lottery mode')" variant="segmented">
            <flux:radio value="official">{{ __('Official') }}</flux:radio>
            <flux:radio value="simulation">{{ __('Simulation') }}</flux:radio>
        </flux:radio.group>

        <div>
            <flux:radio.group wire:model="presentationMode" :label="__('Official reveal mode')" variant="segmented">
                <flux:radio value="manual">{{ __('Manual') }}</flux:radio>
                <flux:radio value="autoplay">{{ __('Autoplay') }}</flux:radio>
            </flux:radio.group>
            <p class="mt-2 text-xs leading-5 text-ink-muted">{{ __('This setting applies only to official lottery presentations and replays. Simulations continue to show results immediately.') }}</p>
        </div>
    </div>

    <div class="grid gap-3 text-sm lg:grid-cols-2">
        <div class="rounded-xl border border-brand/35 bg-brand/10 p-4">
            <strong class="text-rose-200">{{ __('Official') }}</strong>
            <p class="mt-1 leading-6 text-ink-secondary">{{ __('Runs once and saves a definitive result. The saved result can be replayed but not recalculated.') }}</p>
        </div>
        <div class="rounded-xl border border-line bg-raised p-4">
            <strong class="text-ink">{{ __('Simulation') }}</strong>
            <p class="mt-1 leading-6 text-ink-secondary">{{ __('Can be run repeatedly to test possible outcomes. Simulation results are not saved as official results.') }}</p>
        </div>
    </div>

    <section class="space-y-4" aria-labelledby="teams-heading">
        <div class="flex flex-wrap items-end justify-between gap-3">
            <div>
                <flux:heading id="teams-heading" size="lg">{{ __('Teams and lottery odds') }}</flux:heading>
                <flux:text>{{ __('Add teams in their initial draft order. Odds accept up to two decimal places.') }}</flux:text>
            </div>
            <flux:button type="button" icon="plus" wire:click="addTeam" wire:loading.attr="disabled">{{ __('Add team') }}</flux:button>
        </div>

        @error('teams')
            <p class="text-sm text-danger" role="alert">{{ $message }}</p>
        @enderror

        <div class="space-y-3">
            @forelse ($teams as $teamKey => $team)
                <div class="grid gap-4 rounded-2xl border border-line bg-surface p-4 lg:grid-cols-[auto_1fr_10rem_10rem_auto] lg:items-start" wire:key="team-{{ $teamKey }}">
                    <div class="grid size-9 place-items-center rounded-lg bg-raised text-sm font-bold text-ink-secondary lg:mt-7" aria-label="{{ __('Initial position :position', ['position' => $loop->iteration]) }}">
                        #{{ $loop->iteration }}
                    </div>

                    <flux:input
                        wire:model="teams.{{ $teamKey }}.name"
                        :label="__('Team name')"
                        type="text"
                        maxlength="120"
                        required
                    />

                    <flux:input
                        wire:model="teams.{{ $teamKey }}.abbreviation"
                        :label="__('Abbreviation')"
                        type="text"
                        maxlength="10"
                        placeholder="WOL"
                    />

                    <flux:input
                        wire:model="teams.{{ $teamKey }}.percentage"
                        data-odds-input
                        :label="__('Lottery odds (%)')"
                        type="text"
                        inputmode="decimal"
                        placeholder="25.00"
                        required
                    />

                    <div class="flex flex-wrap gap-1 lg:pt-7">
                        <flux:button
                            type="button"
                            size="sm"
                            variant="danger"
                            icon="trash"
                            :aria-label="__('Remove :team', ['team' => $team['name'] ?: __('team')])"
                            wire:click="removeTeam('{{ $teamKey }}')"
                            wire:loading.attr="disabled"
                        />
                    </div>
                </div>
            @empty
                <div class="rounded-xl border border-dashed border-line p-8 text-center text-ink-secondary">
                    {{ __('No teams have been added. A draft may be saved without teams.') }}
                </div>
            @endforelse
        </div>

        <div class="rounded-xl border border-line bg-raised p-4" aria-live="polite">
            <div class="flex items-center justify-between gap-4">
                <span class="font-medium">{{ __('Current total') }}</span>
                <strong class="text-lg tabular-nums text-gold" x-text="format(totalBasisPoints)">0.00%</strong>
            </div>
            <p class="mt-1 text-sm text-gold" x-show="totalBasisPoints < 10000" x-text="`Remaining: ${format(10000 - totalBasisPoints)}`"></p>
            <p class="mt-1 text-sm text-danger" x-show="totalBasisPoints > 10000" x-text="`Excess: ${format(totalBasisPoints - 10000)}`"></p>
            <p class="mt-1 text-sm text-emerald-300" x-show="totalBasisPoints === 10000">{{ __('Valid total: exactly 100.00%.') }}</p>
        </div>

        @error('teams_total')
            <p class="text-sm text-danger" role="alert">{{ $message }}</p>
        @enderror
    </section>
</div>
