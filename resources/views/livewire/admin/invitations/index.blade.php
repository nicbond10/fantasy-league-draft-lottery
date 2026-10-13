<div class="mx-auto max-w-7xl space-y-7">
    <header>
        <p class="eyebrow">{{ __('Administration') }}</p>
        <h1 class="mt-2 text-3xl font-semibold tracking-tight text-ink">{{ __('Invitations') }}</h1>
        <p class="mt-2 text-ink-secondary">{{ __('Invite people to create private accounts. Acceptance links are sent by email and are never displayed here.') }}</p>
    </header>

    <form wire:submit="invite" class="app-card flex max-w-3xl flex-col gap-4 p-5 sm:flex-row sm:items-end sm:p-6">
        <div class="flex-1">
            <flux:input wire:model.blur="email" type="email" :label="__('Email address')" autocomplete="email" required />
        </div>
        <flux:button type="submit" variant="primary" wire:loading.attr="disabled" wire:target="invite">
            <span wire:loading.remove wire:target="invite">{{ __('Send invitation') }}</span>
            <span wire:loading wire:target="invite">{{ __('Sending…') }}</span>
        </flux:button>
    </form>

    @error('invitation')
        <p class="rounded-xl border border-danger/35 bg-danger/10 p-4 text-sm text-rose-200" role="alert">{{ $message }}</p>
    @enderror

    <div class="app-card overflow-x-auto">
        <table class="w-full min-w-[64rem] text-left text-sm">
            <thead class="bg-raised text-ink-muted">
                <tr>
                    <th class="px-4 py-3 font-medium sm:px-6" scope="col">{{ __('Email') }}</th>
                    <th class="px-4 py-3 font-medium" scope="col">{{ __('Invited by') }}</th>
                    <th class="px-4 py-3 font-medium" scope="col">{{ __('State') }}</th>
                    <th class="px-4 py-3 font-medium" scope="col">{{ __('Created') }}</th>
                    <th class="px-4 py-3 font-medium" scope="col">{{ __('Expires') }}</th>
                    <th class="px-4 py-3 font-medium" scope="col">{{ __('Accepted') }}</th>
                    <th class="px-4 py-3 text-right font-medium sm:px-6" scope="col">{{ __('Actions') }}</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-line">
                @foreach ($invitations as $invitation)
                    @php
                        $status = $invitation->accepted_at !== null ? 'accepted' : ($invitation->revoked_at !== null ? 'revoked' : ($invitation->expires_at->isPast() ? 'expired' : 'pending'));
                        $statusStyle = match ($status) {
                            'pending' => 'border-gold/35 bg-gold/10 text-gold',
                            'accepted' => 'border-success/35 bg-success/10 text-emerald-300',
                            'revoked' => 'border-danger/35 bg-danger/10 text-rose-200',
                            default => 'border-line bg-raised text-ink-muted',
                        };
                    @endphp
                    <tr wire:key="invitation-{{ $invitation->id }}">
                        <td class="px-4 py-4 font-semibold text-ink sm:px-6">{{ $invitation->email }}</td>
                        <td class="px-4 py-4 text-ink-secondary">{{ $invitation->invitedBy?->name ?? __('Deleted administrator') }}</td>
                        <td class="px-4 py-4"><span class="inline-flex rounded-full border px-2.5 py-1 text-xs font-semibold {{ $statusStyle }}">{{ ucfirst($status) }}</span></td>
                        <td class="px-4 py-4 text-ink-secondary">{{ $invitation->created_at->format('M j, Y g:i A') }}</td>
                        <td class="px-4 py-4 text-ink-secondary">{{ $invitation->expires_at->format('M j, Y g:i A') }}</td>
                        <td class="px-4 py-4 text-ink-secondary">{{ $invitation->accepted_at?->format('M j, Y g:i A') ?? '—' }}</td>
                        <td class="px-4 py-4 text-right sm:px-6">
                            @if ($invitation->isPending())
                                <flux:button size="sm" variant="danger" wire:click="revoke({{ $invitation->id }})" wire:loading.attr="disabled" wire:target="revoke({{ $invitation->id }})" wire:confirm="{{ __('Revoke this invitation? Its current acceptance link will stop working.') }}">{{ __('Revoke') }}</flux:button>
                            @elseif (in_array($status, ['expired', 'revoked'], true))
                                <flux:button size="sm" wire:click="resend({{ $invitation->id }})" wire:loading.attr="disabled" wire:target="resend({{ $invitation->id }})">{{ __('Send new link') }}</flux:button>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    {{ $invitations->links() }}
</div>
