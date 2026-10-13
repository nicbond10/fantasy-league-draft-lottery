<div class="mx-auto max-w-6xl space-y-8">
    <div>
        <p class="eyebrow">{{ __('Lottery builder') }}</p>
        <h1 class="mt-2 text-3xl font-semibold tracking-tight text-ink">{{ __('Create lottery') }}</h1>
        <p class="mt-2 text-ink-secondary">{{ __('Save an incomplete configuration as a draft, or mark a valid configuration ready.') }}</p>
    </div>

    @include('livewire.lotteries.partials.form-fields')

    <div class="flex flex-col-reverse gap-3 border-t border-line pt-6 sm:flex-row sm:justify-end">
        <flux:button :href="route('lotteries.index')" wire:navigate>{{ __('Cancel') }}</flux:button>
        <flux:button type="button" wire:click="saveDraft" wire:loading.attr="disabled" wire:target="saveDraft">{{ __('Save Draft') }}</flux:button>
        <flux:button type="button" variant="primary" wire:click="saveReady" wire:loading.attr="disabled" wire:target="saveReady">
            <span wire:loading.remove wire:target="saveReady">{{ __('Save and Mark Ready') }}</span>
            <span wire:loading wire:target="saveReady">{{ __('Validating…') }}</span>
        </flux:button>
    </div>
</div>
