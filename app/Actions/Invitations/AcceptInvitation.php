<?php

declare(strict_types=1);

namespace App\Actions\Invitations;

use App\Concerns\PasswordValidationRules;
use App\Enums\UserStatus;
use App\Models\User;
use App\Models\UserInvitation;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

final class AcceptInvitation
{
    use PasswordValidationRules;

    public function handle(string $rawToken, string $name, string $password, string $passwordConfirmation): User
    {
        $validated = Validator::make([
            'name' => $name,
            'password' => $password,
            'password_confirmation' => $passwordConfirmation,
        ], [
            'name' => ['required', 'string', 'max:255'],
            'password' => $this->passwordRules(),
        ])->validate();

        $user = DB::transaction(function () use ($rawToken, $validated): User {
            $invitation = UserInvitation::query()
                ->where('token_hash', hash('sha256', $rawToken))
                ->lockForUpdate()
                ->first();

            if (! $invitation?->isPending()) {
                throw ValidationException::withMessages([
                    'invitation' => 'This invitation is invalid or no longer available.',
                ]);
            }

            if (User::query()->whereRaw('LOWER(email) = ?', [$invitation->email])->exists()) {
                throw ValidationException::withMessages([
                    'invitation' => 'This invitation is invalid or no longer available.',
                ]);
            }

            $user = User::query()->create([
                'name' => $validated['name'],
                'email' => $invitation->email,
                'password' => $validated['password'],
                'email_verified_at' => now(),
                'status' => UserStatus::Active,
                'is_platform_admin' => false,
            ]);

            $invitation->forceFill(['accepted_at' => now()])->save();

            return $user;
        }, attempts: 3);

        Log::info('User invitation accepted.', ['user_id' => $user->id]);

        return $user;
    }
}
