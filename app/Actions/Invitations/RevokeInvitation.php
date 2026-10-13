<?php

declare(strict_types=1);

namespace App\Actions\Invitations;

use App\Models\User;
use App\Models\UserInvitation;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

final class RevokeInvitation
{
    public function handle(User $administrator, UserInvitation $invitation): void
    {
        abort_unless($administrator->isActive() && $administrator->isPlatformAdministrator(), 403);

        DB::transaction(function () use ($invitation): void {
            $lockedInvitation = UserInvitation::query()->lockForUpdate()->findOrFail($invitation->id);

            if (! $lockedInvitation->isPending()) {
                throw ValidationException::withMessages([
                    'invitation' => 'Only a pending invitation can be revoked.',
                ]);
            }

            $lockedInvitation->forceFill(['revoked_at' => now()])->save();
        }, attempts: 3);

        Log::info('User invitation revoked.', [
            'invitation_id' => $invitation->id,
            'revoked_by_user_id' => $administrator->id,
        ]);
    }
}
