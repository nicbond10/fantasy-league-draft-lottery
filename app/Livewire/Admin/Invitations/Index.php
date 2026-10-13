<?php

declare(strict_types=1);

namespace App\Livewire\Admin\Invitations;

use App\Actions\Invitations\InviteUser as InviteUserAction;
use App\Actions\Invitations\RevokeInvitation;
use App\Models\User;
use App\Models\UserInvitation;
use App\Support\NormalizedEmail;
use Flux\Flux;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\RateLimiter;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Title('Manage invitations')]
#[Layout('layouts.app')]
final class Index extends Component
{
    use WithPagination;

    public string $email = '';

    public function invite(InviteUserAction $inviteUser): void
    {
        $administrator = auth()->user();
        abort_unless($administrator instanceof User, 403);
        if (! $this->consumeInvitationRateLimit($administrator, $this->email)) {
            $this->addError('email', 'Too many invitations have been requested. Please try again in a minute.');

            return;
        }

        $inviteUser->handle($administrator, $this->email);

        $this->reset('email');
        $this->resetPage();
        Flux::toast(variant: 'success', text: 'Invitation sent.');
    }

    public function resend(int $invitationId, InviteUserAction $inviteUser): void
    {
        $administrator = $this->administrator();

        $invitation = UserInvitation::query()->findOrFail($invitationId);

        if (! $this->consumeInvitationRateLimit($administrator, $invitation->email)) {
            $this->addError('invitation', 'Too many invitations have been requested. Please try again in a minute.');

            return;
        }

        $inviteUser->handle($administrator, $invitation->email);
        $this->resetPage();
        Flux::toast(variant: 'success', text: 'Invitation resent with a new acceptance link.');
    }

    public function revoke(int $invitationId, RevokeInvitation $revokeInvitation): void
    {
        $administrator = auth()->user();
        abort_unless($administrator instanceof User, 403);

        $revokeInvitation->handle(
            $administrator,
            UserInvitation::query()->findOrFail($invitationId),
        );

        Flux::toast(variant: 'success', text: 'Invitation revoked.');
    }

    public function render(): View
    {
        return view('livewire.admin.invitations.index', [
            'invitations' => UserInvitation::query()
                ->with('invitedBy:id,name')
                ->latest()
                ->paginate(15),
        ]);
    }

    private function administrator(): User
    {
        $administrator = auth()->user();
        abort_unless($administrator instanceof User, 403);

        return $administrator;
    }

    private function consumeInvitationRateLimit(User $administrator, string $email): bool
    {
        $ip = request()->ip();
        $administratorKey = 'send-invitation:administrator:'.$administrator->id.'|'.$ip;
        $emailKey = 'send-invitation:email:'.hash('sha256', NormalizedEmail::from($email)).'|'.$ip;

        return RateLimiter::attempt($administratorKey, 10, static fn (): bool => true, 60)
            && RateLimiter::attempt($emailKey, 3, static fn (): bool => true, 60);
    }
}
