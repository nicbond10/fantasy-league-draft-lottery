<?php

declare(strict_types=1);

namespace App\Livewire\Auth;

use App\Actions\Invitations\AcceptInvitation as AcceptInvitationAction;
use App\Models\UserInvitation;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Locked;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Accept invitation')]
#[Layout('layouts.auth')]
final class AcceptInvitation extends Component
{
    #[Locked]
    public string $token = '';

    #[Locked]
    public string $email = '';

    public string $name = '';

    public string $password = '';

    public string $password_confirmation = '';

    public function mount(string $token): void
    {
        $this->token = $token;
        $this->email = UserInvitation::query()
            ->where('token_hash', hash('sha256', $token))
            ->whereNull('accepted_at')
            ->whereNull('revoked_at')
            ->where('expires_at', '>', now())
            ->value('email') ?? '';
    }

    public function accept(AcceptInvitationAction $acceptInvitation): void
    {
        $rateLimitKey = 'accept-invitation:'.hash('sha256', $this->token).'|'.request()->ip();

        if (! RateLimiter::attempt($rateLimitKey, 10, static fn (): bool => true, 60)) {
            $this->addError('invitation', 'Too many attempts. Please try again in a minute.');

            return;
        }

        $user = $acceptInvitation->handle(
            $this->token,
            $this->name,
            $this->password,
            $this->password_confirmation,
        );

        Auth::guard('web')->login($user);
        session()->regenerate();

        $this->redirectRoute('dashboard', navigate: true);
    }

    public function render(): View
    {
        return view('livewire.auth.accept-invitation');
    }
}
