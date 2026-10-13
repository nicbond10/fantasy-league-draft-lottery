<?php

declare(strict_types=1);

namespace App\Livewire\Admin;

use App\Enums\UserStatus;
use App\Models\User;
use App\Models\UserInvitation;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Administration')]
#[Layout('layouts.app')]
final class Dashboard extends Component
{
    public function render(): View
    {
        $userCounts = User::query()
            ->selectRaw('COUNT(*) as total')
            ->selectRaw('SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as active', [UserStatus::Active->value])
            ->selectRaw('SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as suspended', [UserStatus::Suspended->value])
            ->selectRaw('SUM(CASE WHEN is_platform_admin = 1 THEN 1 ELSE 0 END) as administrators')
            ->firstOrFail();

        $invitationCounts = UserInvitation::query()
            ->selectRaw('SUM(CASE WHEN accepted_at IS NULL AND revoked_at IS NULL AND expires_at > ? THEN 1 ELSE 0 END) as pending', [now()])
            ->selectRaw('SUM(CASE WHEN accepted_at IS NULL AND revoked_at IS NULL AND expires_at <= ? THEN 1 ELSE 0 END) as expired', [now()])
            ->firstOrFail();

        return view('livewire.admin.dashboard', [
            'userCount' => (int) $userCounts->getAttribute('total'),
            'activeUserCount' => (int) $userCounts->getAttribute('active'),
            'suspendedUserCount' => (int) $userCounts->getAttribute('suspended'),
            'administratorCount' => (int) $userCounts->getAttribute('administrators'),
            'pendingInvitationCount' => (int) $invitationCounts->getAttribute('pending'),
            'expiredInvitationCount' => (int) $invitationCounts->getAttribute('expired'),
        ]);
    }
}
