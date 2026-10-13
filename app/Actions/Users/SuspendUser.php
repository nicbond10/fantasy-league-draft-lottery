<?php

declare(strict_types=1);

namespace App\Actions\Users;

use App\Enums\UserStatus;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

final class SuspendUser
{
    public function handle(User $administrator, User $user): void
    {
        abort_unless($administrator->isActive() && $administrator->isPlatformAdministrator(), 403);

        if ($administrator->is($user)) {
            throw ValidationException::withMessages([
                'user' => 'You cannot suspend your own account.',
            ]);
        }

        DB::transaction(function () use ($user): void {
            $lockedUser = User::query()->lockForUpdate()->findOrFail($user->id);

            if ($lockedUser->isPlatformAdministrator()) {
                $activeAdministratorCount = User::query()
                    ->where('is_platform_admin', true)
                    ->where('status', UserStatus::Active->value)
                    ->lockForUpdate()
                    ->count();

                if ($activeAdministratorCount <= 1) {
                    throw ValidationException::withMessages([
                        'user' => 'The last active platform administrator cannot be suspended.',
                    ]);
                }
            }

            $lockedUser->forceFill(['status' => UserStatus::Suspended])->save();
        }, attempts: 3);

        Log::notice('User suspended.', [
            'user_id' => $user->id,
            'suspended_by_user_id' => $administrator->id,
        ]);
    }
}
