<?php

use App\Models\User;
use App\Models\UserInvitation;
use Livewire\Livewire;

function routeAuthorizationSnapshot(string $routeName): string
{
    $response = test()->get(route($routeName))->assertOk();
    preg_match('/wire:snapshot="([^"]+)"/', $response->getContent(), $matches);

    expect($matches)->toHaveKey(1);

    return htmlspecialchars_decode($matches[1], ENT_QUOTES | ENT_SUBSTITUTE);
}

function postRouteAuthorizationUpdate(string $snapshot, string $method = '$refresh', array $params = [])
{
    return test()->postJson(Livewire::getUpdateUri(), [
        'components' => [[
            'snapshot' => $snapshot,
            'updates' => [],
            'calls' => [['path' => '', 'method' => $method, 'params' => $params]],
        ]],
    ], ['X-Livewire' => 'true']);
}

test('revoked administrators cannot refresh a previously opened admin page', function (string $routeName) {
    $administrator = User::factory()->platformAdmin()->create();
    $this->actingAs($administrator);
    $snapshot = routeAuthorizationSnapshot($routeName);

    $administrator->update(['is_platform_admin' => false]);
    $this->actingAs($administrator->fresh());

    postRouteAuthorizationUpdate($snapshot)->assertForbidden();
})->with(['admin.dashboard', 'admin.users.index', 'admin.invitations.index']);

test('revoked administrators cannot mutate invitations from an old snapshot', function () {
    $administrator = User::factory()->platformAdmin()->create();
    $invitation = UserInvitation::factory()->create(['invited_by_user_id' => $administrator->id]);
    $this->actingAs($administrator);
    $snapshot = routeAuthorizationSnapshot('admin.invitations.index');

    $administrator->update(['is_platform_admin' => false]);
    $this->actingAs($administrator->fresh());

    postRouteAuthorizationUpdate($snapshot, 'revoke', [$invitation->id])->assertForbidden();
    expect($invitation->fresh()->revoked_at)->toBeNull();
});

test('verification is required again for subsequent protected page requests', function () {
    $user = User::factory()->create();
    $this->actingAs($user);
    $snapshot = routeAuthorizationSnapshot('lotteries.index');

    $user->update(['email_verified_at' => null]);
    $this->actingAs($user->fresh());

    postRouteAuthorizationUpdate($snapshot)->assertForbidden();
});

test('expired password confirmation blocks subsequent security mutations', function () {
    $user = User::factory()->create();
    $this->actingAs($user)->withSession(['auth.password_confirmed_at' => now()->timestamp]);
    $snapshot = routeAuthorizationSnapshot('security.edit');

    $this->withSession(['auth.password_confirmed_at' => now()->subHours(4)->timestamp]);

    postRouteAuthorizationUpdate($snapshot, 'enable')
        ->assertStatus(423);
    expect($user->fresh()->two_factor_secret)->toBeNull();
});

test('confirmed security requests and unverified profile requests remain available', function () {
    $user = User::factory()->create();
    $this->actingAs($user)->withSession(['auth.password_confirmed_at' => now()->timestamp]);
    $snapshot = routeAuthorizationSnapshot('security.edit');
    postRouteAuthorizationUpdate($snapshot)->assertOk();

    $user->update(['email_verified_at' => null]);
    $this->actingAs($user->fresh());
    $snapshot = routeAuthorizationSnapshot('profile.edit');
    postRouteAuthorizationUpdate($snapshot)->assertOk();
});
