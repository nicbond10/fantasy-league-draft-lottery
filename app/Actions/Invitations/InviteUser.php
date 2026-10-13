<?php

declare(strict_types=1);

namespace App\Actions\Invitations;

use App\Mail\UserInvitationMail;
use App\Models\User;
use App\Models\UserInvitation;
use App\Support\NormalizedEmail;
use Illuminate\Contracts\Mail\Mailer;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

final readonly class InviteUser
{
    public function __construct(private Mailer $mailer) {}

    public function handle(User $administrator, string $email): UserInvitation
    {
        $this->authorize($administrator);

        $email = NormalizedEmail::from($email);

        Validator::make(['email' => $email], [
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique(User::class)],
        ])->validate();

        if (User::query()->whereRaw('LOWER(email) = ?', [$email])->exists()) {
            throw ValidationException::withMessages([
                'email' => 'An account already exists for this email address.',
            ]);
        }

        $rawToken = Str::random(64);

        $invitation = DB::transaction(function () use ($administrator, $email, $rawToken): UserInvitation {
            $invitation = UserInvitation::query()
                ->where('email', $email)
                ->lockForUpdate()
                ->first();

            if ($invitation?->isPending()) {
                throw ValidationException::withMessages([
                    'email' => 'A pending invitation already exists for this email address.',
                ]);
            }

            if ($invitation?->accepted_at !== null) {
                throw ValidationException::withMessages([
                    'email' => 'An account already exists for this email address.',
                ]);
            }

            $invitation ??= new UserInvitation;
            $invitation->fill([
                'email' => $email,
                'token_hash' => hash('sha256', $rawToken),
                'invited_by_user_id' => $administrator->id,
                'expires_at' => now()->addDays((int) config('invitations.expires_in_days')),
                'accepted_at' => null,
                'revoked_at' => null,
            ]);
            $invitation->save();

            return $invitation;
        }, attempts: 3);

        $this->mailer->send(
            (new UserInvitationMail($rawToken, $invitation->expires_at))->to($invitation->email),
        );

        Log::info('User invitation sent.', [
            'invitation_id' => $invitation->id,
            'invited_by_user_id' => $administrator->id,
            'email_hash' => hash('sha256', $invitation->email),
        ]);

        return $invitation;
    }

    private function authorize(User $administrator): void
    {
        abort_unless($administrator->isActive() && $administrator->isPlatformAdministrator(), 403);
    }
}
