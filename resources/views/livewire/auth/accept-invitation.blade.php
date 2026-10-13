<div class="flex flex-col gap-6">
        @if ($email !== '')
            <x-auth-header
                :title="__('Create your account')"
                :description="__('Set up the account invited for :email.', ['email' => $email])"
            />

            <form wire:submit="accept" class="flex flex-col gap-6">
                <flux:input
                    wire:model.blur="name"
                    :label="__('Name')"
                    type="text"
                    required
                    autofocus
                    autocomplete="name"
                />

                <flux:input
                    :label="__('Email address')"
                    type="email"
                    :value="$email"
                    disabled
                />

                <flux:input
                    wire:model="password"
                    :label="__('Password')"
                    type="password"
                    required
                    autocomplete="new-password"
                    viewable
                />

                <flux:input
                    wire:model="password_confirmation"
                    :label="__('Confirm password')"
                    type="password"
                    required
                    autocomplete="new-password"
                    viewable
                />

                @error('invitation')
                    <p class="text-sm text-red-600 dark:text-red-400" role="alert">{{ $message }}</p>
                @enderror

                <flux:button type="submit" variant="primary" wire:loading.attr="disabled">
                    <span wire:loading.remove wire:target="accept">{{ __('Create account') }}</span>
                    <span wire:loading wire:target="accept">{{ __('Creating account…') }}</span>
                </flux:button>
            </form>
        @else
            <x-auth-header
                :title="__('Invitation unavailable')"
                :description="__('This invitation is invalid, expired, accepted, or revoked.')"
            />

            <flux:button :href="route('login')" variant="primary" wire:navigate>
                {{ __('Return to login') }}
            </flux:button>
        @endif
</div>
