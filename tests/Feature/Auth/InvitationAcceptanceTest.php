<?php

use App\Actions\Invitations\AcceptInvitation;
use App\Actions\Invitations\InviteUser;
use App\Livewire\Auth\AcceptInvitation as AcceptInvitationComponent;
use App\Mail\UserInvitationMail;
use App\Models\User;
use App\Models\UserInvitation;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\ValidationException;
use Livewire\Livewire;

test('an invited user can create a verified account', function () {
    $rawToken = 'valid-invitation-token';
    UserInvitation::factory()->create([
        'email' => 'Invited@Example.COM',
        'token_hash' => hash('sha256', $rawToken),
    ]);

    Livewire::test(AcceptInvitationComponent::class, ['token' => $rawToken])
        ->set('name', 'Invited User')
        ->set('password', 'password')
        ->set('password_confirmation', 'password')
        ->call('accept')
        ->assertHasNoErrors()
        ->assertRedirect(route('dashboard'));

    $user = User::query()->where('email', 'invited@example.com')->firstOrFail();

    expect($user->email_verified_at)->not->toBeNull()
        ->and($user->is_platform_admin)->toBeFalse()
        ->and(UserInvitation::query()->firstOrFail()->accepted_at)->not->toBeNull();

    $this->assertAuthenticatedAs($user);
});

test('expired invitations cannot be accepted', function () {
    $rawToken = 'expired-invitation-token';
    UserInvitation::factory()->expired()->create(['token_hash' => hash('sha256', $rawToken)]);

    expect(fn () => app(AcceptInvitation::class)->handle($rawToken, 'User', 'password', 'password'))
        ->toThrow(ValidationException::class);

    expect(User::query()->count())->toBe(1);
});

test('revoked invitations cannot be accepted', function () {
    $rawToken = 'revoked-invitation-token';
    UserInvitation::factory()->revoked()->create(['token_hash' => hash('sha256', $rawToken)]);

    expect(fn () => app(AcceptInvitation::class)->handle($rawToken, 'User', 'password', 'password'))
        ->toThrow(ValidationException::class);

    expect(User::query()->count())->toBe(1);
});

test('accepted invitation tokens cannot be reused', function () {
    $rawToken = 'single-use-invitation-token';
    UserInvitation::factory()->create(['token_hash' => hash('sha256', $rawToken)]);
    $action = app(AcceptInvitation::class);

    $action->handle($rawToken, 'First User', 'password', 'password');

    expect(fn () => $action->handle($rawToken, 'Second User', 'password', 'password'))
        ->toThrow(ValidationException::class)
        ->and(User::query()->where('email', UserInvitation::query()->firstOrFail()->email)->count())->toBe(1);
});

test('invitation emails are normalized and only one usable invitation may exist', function () {
    Mail::fake();
    $administrator = User::factory()->platformAdmin()->create();
    $action = app(InviteUser::class);

    $invitation = $action->handle($administrator, '  Commissioner@Example.COM ');

    expect($invitation->email)->toBe('commissioner@example.com');
    Mail::assertSent(UserInvitationMail::class, fn (UserInvitationMail $mail): bool => $mail->hasTo('commissioner@example.com'));

    expect(fn () => $action->handle($administrator, 'COMMISSIONER@example.com'))
        ->toThrow(ValidationException::class)
        ->and(UserInvitation::query()->where('email', 'commissioner@example.com')->count())->toBe(1);
});

test('user email writes are normalized consistently', function () {
    $user = User::factory()->create(['email' => '  Owner@Example.COM ']);

    expect($user->email)->toBe('owner@example.com')
        ->and(User::query()->where('email', 'owner@example.com')->exists())->toBeTrue();
});

test('an expired invitation can be replaced with a newly usable token', function () {
    Mail::fake();
    $administrator = User::factory()->platformAdmin()->create();
    $oldInvitation = UserInvitation::factory()->expired()->create([
        'email' => 'person@example.com',
        'invited_by_user_id' => $administrator->id,
    ]);

    $newInvitation = app(InviteUser::class)->handle($administrator, 'PERSON@example.com');

    expect($newInvitation->is($oldInvitation))->toBeTrue()
        ->and($newInvitation->isPending())->toBeTrue()
        ->and(UserInvitation::query()->where('email', 'person@example.com')->count())->toBe(1);
});
