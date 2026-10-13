<?php

use App\Actions\Invitations\AcceptInvitation;
use App\Actions\Invitations\RevokeInvitation;
use App\Models\User;
use App\Models\UserInvitation;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpException;

test('an administrator can revoke a pending invitation', function () {
    $administrator = User::factory()->platformAdmin()->create();
    $rawToken = 'revoked-by-admin-token';
    $invitation = UserInvitation::factory()->create([
        'invited_by_user_id' => $administrator->id,
        'token_hash' => hash('sha256', $rawToken),
    ]);

    app(RevokeInvitation::class)->handle($administrator, $invitation);

    expect($invitation->refresh()->revoked_at)->not->toBeNull();
    expect(fn () => app(AcceptInvitation::class)->handle($rawToken, 'User', 'password', 'password'))
        ->toThrow(ValidationException::class);
});

test('a non administrator cannot revoke invitations', function () {
    $user = User::factory()->create();
    $invitation = UserInvitation::factory()->create();

    expect(fn () => app(RevokeInvitation::class)->handle($user, $invitation))
        ->toThrow(HttpException::class);
});
