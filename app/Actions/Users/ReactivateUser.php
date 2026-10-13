<?php

declare(strict_types=1);

namespace App\Actions\Users;

use App\Enums\UserStatus;
use App\Models\User;
use Illuminate\Support\Facades\Log;

final class ReactivateUser
{
    public function handle(User $administrator, User $user): void
    {
        abort_unless($administrator->isActive() && $administrator->isPlatformAdministrator(), 403);

        $user->forceFill(['status' => UserStatus::Active])->save();

        Log::info('User reactivated.', [
            'user_id' => $user->id,
            'reactivated_by_user_id' => $administrator->id,
        ]);
    }
}
