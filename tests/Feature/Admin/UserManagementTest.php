<?php

use App\Actions\Users\ReactivateUser;
use App\Actions\Users\SuspendUser;
use App\Enums\UserStatus;
use App\Models\User;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpException;

test('an administrator can suspend and reactivate a user', function () {
    $administrator = User::factory()->platformAdmin()->create();
    $user = User::factory()->create();

    app(SuspendUser::class)->handle($administrator, $user);
    expect($user->refresh()->status)->toBe(UserStatus::Suspended);

    app(ReactivateUser::class)->handle($administrator, $user);
    expect($user->refresh()->status)->toBe(UserStatus::Active);
});

test('an administrator cannot suspend their own account', function () {
    $administrator = User::factory()->platformAdmin()->create();

    expect(fn () => app(SuspendUser::class)->handle($administrator, $administrator))
        ->toThrow(ValidationException::class)
        ->and($administrator->refresh()->status)->toBe(UserStatus::Active);
});

test('the last active administrator cannot be suspended', function () {
    $actingAdministrator = User::factory()->platformAdmin()->make();
    $lastActiveAdministrator = User::factory()->platformAdmin()->create();

    expect(fn () => app(SuspendUser::class)->handle($actingAdministrator, $lastActiveAdministrator))
        ->toThrow(ValidationException::class)
        ->and($lastActiveAdministrator->refresh()->status)->toBe(UserStatus::Active);
});

test('a non administrator cannot invoke user management actions', function () {
    $user = User::factory()->create();
    $target = User::factory()->create();

    expect(fn () => app(SuspendUser::class)->handle($user, $target))
        ->toThrow(HttpException::class);
});
