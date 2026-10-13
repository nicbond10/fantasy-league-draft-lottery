<div class="mx-auto max-w-7xl space-y-8">
    <header>
        <p class="eyebrow">{{ __('Platform operations') }}</p>
        <h1 class="mt-2 text-3xl font-semibold tracking-tight text-ink">{{ __('Administration') }}</h1>
        <p class="mt-2 text-ink-secondary">{{ __('Manage platform access and invitations. Private user lotteries remain outside administrator access.') }}</p>
    </header>

    <section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3" aria-label="{{ __('Administration summary') }}">
        @foreach ([
            [__('Total users'), $userCount],
            [__('Active users'), $activeUserCount],
            [__('Suspended users'), $suspendedUserCount],
            [__('Platform administrators'), $administratorCount],
            [__('Pending usable invitations'), $pendingInvitationCount],
            [__('Expired invitations'), $expiredInvitationCount],
        ] as [$label, $value])
            <article class="app-card p-5">
                <p class="text-sm font-medium text-ink-secondary">{{ $label }}</p>
                <p class="mt-3 text-3xl font-semibold tabular-nums {{ $label === __('Pending usable invitations') ? 'text-gold' : 'text-ink' }}">{{ $value }}</p>
            </article>
        @endforeach
    </section>

    <div class="flex flex-wrap gap-3">
        <flux:button :href="route('admin.users.index')" wire:navigate>{{ __('Manage users') }}</flux:button>
        <flux:button :href="route('admin.invitations.index')" variant="primary" wire:navigate>{{ __('Manage invitations') }}</flux:button>
    </div>
</div>
