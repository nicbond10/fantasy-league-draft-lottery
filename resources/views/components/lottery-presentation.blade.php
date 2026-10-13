<dialog
    x-ref="presentationDialog"
    x-on:cancel.prevent="close"
    x-on:close="cleanupTimers"
    class="presentation-dialog fixed inset-0 m-0 h-dvh max-h-none w-screen max-w-none overflow-hidden border-0 bg-app p-0 text-ink"
    aria-labelledby="presentation-title"
    aria-describedby="presentation-description"
>
    <div class="flex h-full flex-col">
        <header class="flex items-center justify-between gap-4 border-b border-line bg-shell px-4 py-4 sm:px-8">
            <div>
                <p class="eyebrow" x-text="source === 'simulation' ? '{{ __('Simulation') }}' : (source === 'replay' ? '{{ __('Official Draft Lottery Replay') }}' : '{{ __('Official Draft Lottery') }}')"></p>
                <h2 id="presentation-title" class="mt-1 text-xl font-semibold text-ink sm:text-2xl" x-text="source === 'simulation' ? '{{ __('Simulation Result') }}' : '{{ __('Draft Lottery Presentation') }}'"></h2>
                <p id="presentation-description" class="mt-1 text-sm text-ink-secondary" x-text="source === 'simulation' ? '{{ __('Not an official saved result') }}' : (source === 'replay' ? '{{ __('This replay uses the saved official result.') }}' : '{{ __('The official result has been generated and saved.') }}')"></p>
            </div>
            <button type="button" class="grid size-11 shrink-0 place-items-center rounded-xl text-ink-secondary hover:bg-raised hover:text-ink" x-on:click="close" aria-label="{{ __('Close presentation') }}">
                <flux:icon.x-mark class="size-5" aria-hidden="true" />
            </button>
        </header>

        <div class="min-h-0 flex-1 overflow-y-auto px-4 py-5 sm:px-8 lg:px-12 lg:py-8">
            <div class="mx-auto max-w-5xl space-y-5">
                <section x-show="state === 'ready'" x-cloak class="rounded-2xl border border-line bg-surface p-5 sm:p-6" aria-labelledby="presentation-ready-title">
                    <p class="eyebrow" x-text="source === 'replay' ? '{{ __('Saved result ready') }}' : '{{ __('Official result secured') }}'"></p>
                    <h3 id="presentation-ready-title" class="mt-2 text-2xl font-semibold tracking-tight text-ink sm:text-3xl" x-text="source === 'replay' ? '{{ __('Ready to replay the lottery?') }}' : '{{ __('Ready to present the lottery?') }}'"></h3>
                    <p id="reveal-mode-instructions" class="mt-3 max-w-3xl text-sm leading-6 text-ink-secondary">{{ __('Choose Manual to reveal one pick at a time, or Autoplay for a timed reveal. No team is disclosed until its pick is revealed.') }}</p>

                    <fieldset class="mt-5" aria-describedby="reveal-mode-instructions">
                        <legend class="text-sm font-semibold text-ink">{{ __('Presentation mode') }}</legend>
                        <div class="mt-2 flex flex-wrap gap-3">
                            <label class="flex min-h-11 items-center gap-2 rounded-xl border border-line bg-raised px-4 text-sm font-semibold text-ink has-checked:border-brand has-checked:bg-brand/10">
                                <input type="radio" name="reveal-mode" value="manual" x-model="revealMode" :disabled="state !== 'ready'" />
                                {{ __('Manual') }}
                            </label>
                            <label class="flex min-h-11 items-center gap-2 rounded-xl border border-line bg-raised px-4 text-sm font-semibold text-ink has-checked:border-brand has-checked:bg-brand/10">
                                <input type="radio" name="reveal-mode" value="autoplay" x-model="revealMode" :disabled="state !== 'ready'" />
                                {{ __('Autoplay') }}
                            </label>
                        </div>
                    </fieldset>

                    <div class="mt-5 flex flex-col gap-3 sm:flex-row">
                        <button x-ref="startButton" type="button" class="min-h-12 rounded-xl bg-gold px-6 text-sm font-semibold text-stone-950 hover:bg-gold-hover" x-on:click="startOfficial" x-text="source === 'replay' ? '{{ __('Start Replay') }}' : '{{ __('Start Lottery') }}'"></button>
                        <button type="button" class="min-h-12 rounded-xl border border-line bg-raised px-6 text-sm font-semibold text-ink hover:border-ink-muted" x-on:click="skip">{{ __('Show Final Result') }}</button>
                    </div>
                </section>

                <ol x-show="state !== 'idle'" x-cloak class="grid gap-2" aria-label="{{ __('Draft positions') }}">
                    <template x-for="position in orderedPositions()" :key="position.final_position">
                        <li class="flex min-h-20 items-center gap-4 rounded-2xl border p-4 transition-colors" :class="position.winner && isRevealed(position.final_position) ? 'border-gold/50 bg-gold/10 shadow-[0_0_0_1px_rgb(255_179_68_/_0.2)]' : 'border-line bg-surface'">
                            <span class="grid size-11 shrink-0 place-items-center rounded-xl font-bold tabular-nums" :class="position.winner && isRevealed(position.final_position) ? 'bg-gold text-stone-950' : 'bg-raised text-ink-secondary'">
                                <span class="sr-only">{{ __('Pick') }}</span>
                                <span x-text="position.final_position"></span>
                            </span>
                            <span class="min-w-0 flex-1">
                                <template x-if="!isRevealed(position.final_position)">
                                    <span class="block font-medium text-ink-muted">{{ __('Not revealed') }}</span>
                                </template>
                                <template x-if="isRevealed(position.final_position)">
                                    <span class="presentation-entry block">
                                        <span class="block break-words text-base font-semibold text-ink sm:text-lg" x-text="position.team_name"></span>
                                        <span x-show="position.winner" class="mt-1 block text-xs font-semibold tracking-[0.14em] text-gold uppercase">{{ __('First overall pick winner') }}</span>
                                    </span>
                                </template>
                            </span>
                            <template x-if="isRevealed(position.final_position)">
                                <span class="hidden text-sm tabular-nums text-ink-secondary sm:block" x-text="`${position.percentage}% odds`"></span>
                            </template>
                        </li>
                    </template>
                </ol>
            </div>
        </div>

        <p class="sr-only" aria-live="polite" aria-atomic="true" x-text="statusMessage"></p>

        <footer class="flex flex-wrap items-center justify-between gap-3 border-t border-line bg-shell px-4 py-4 sm:px-8">
            <div x-show="state === 'revealing'" x-cloak class="flex flex-wrap items-center gap-3">
                <button
                    x-ref="revealButton"
                    type="button"
                    class="min-h-11 rounded-xl bg-gold px-5 text-sm font-semibold text-stone-950 hover:bg-gold-hover disabled:opacity-60"
                    x-show="revealMode === 'manual'"
                    x-on:click="revealNextManual"
                    x-bind:disabled="revealLocked || nextRevealPosition === null"
                    x-text="revealButtonLabel()"
                ></button>
                <span x-show="revealMode === 'autoplay'" class="text-sm font-semibold text-ink-secondary">{{ __('Autoplay is revealing picks from the final position toward #1.') }}</span>
                <button type="button" class="min-h-11 rounded-xl border border-line bg-raised px-4 text-sm font-semibold text-ink hover:border-ink-muted" x-on:click="skip">{{ __('Skip Presentation') }}</button>
            </div>

            <span x-show="state === 'complete'" x-cloak class="text-sm font-semibold text-gold">{{ __('Complete final order') }}</span>

            <div class="ms-auto flex flex-wrap justify-end gap-3" x-show="state === 'complete'" x-cloak>
                <button x-show="source === 'simulation'" type="button" class="min-h-11 rounded-xl bg-gold px-4 text-sm font-semibold text-stone-950 hover:bg-gold-hover disabled:opacity-60" x-on:click="simulateAgain" x-bind:disabled="simulationPending" x-text="simulationPending ? '{{ __('Simulating…') }}' : '{{ __('Simulate Again') }}'"></button>
                <button x-show="source !== 'simulation'" type="button" class="min-h-11 rounded-xl border border-line bg-raised px-4 text-sm font-semibold text-ink hover:border-brand hover:bg-brand/10" x-on:click="replayPresentation">{{ __('Replay Presentation') }}</button>
                <button x-ref="closeButton" type="button" class="min-h-11 rounded-xl bg-brand px-4 text-sm font-semibold text-ink hover:bg-brand-hover" x-on:click="close" x-text="source === 'simulation' ? '{{ __('Close') }}' : '{{ __('View Final Order') }}'"></button>
            </div>
        </footer>
    </div>
</dialog>
