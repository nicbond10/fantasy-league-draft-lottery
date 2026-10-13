<?php

use App\Enums\UserStatus;
use App\Livewire\Admin\Dashboard as AdminDashboard;
use App\Livewire\Admin\Invitations\Index as InvitationIndex;
use App\Mail\UserInvitationMail;
use App\Models\Lottery;
use App\Models\LotteryDraw;
use App\Models\LotteryDrawResult;
use App\Models\User;
use App\Models\UserInvitation;
use Database\Seeders\DevelopmentSampleSeeder;
use Database\Seeders\PlatformAdministratorSeeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Livewire\Livewire;

test('admin dashboard reports the complete access and invitation overview', function () {
    $administrator = User::factory()->platformAdmin()->create();
    User::factory()->create();
    User::factory()->suspended()->create();
    UserInvitation::factory()->create(['invited_by_user_id' => $administrator->id]);
    UserInvitation::factory()->expired()->create(['invited_by_user_id' => $administrator->id]);

    Livewire::actingAs($administrator)->test(AdminDashboard::class)
        ->assertViewHas('userCount', 3)
        ->assertViewHas('activeUserCount', 2)
        ->assertViewHas('suspendedUserCount', 1)
        ->assertViewHas('administratorCount', 1)
        ->assertViewHas('pendingInvitationCount', 1)
        ->assertViewHas('expiredInvitationCount', 1);
});

test('invitation administration renders lifecycle states without raw tokens', function () {
    $administrator = User::factory()->platformAdmin()->create();
    $rawToken = 'never-render-this-raw-invitation-token';
    UserInvitation::factory()->create([
        'email' => 'pending@example.test',
        'invited_by_user_id' => $administrator->id,
        'token_hash' => hash('sha256', $rawToken),
    ]);
    UserInvitation::factory()->accepted()->create(['email' => 'accepted@example.test', 'invited_by_user_id' => $administrator->id]);
    UserInvitation::factory()->expired()->create(['email' => 'expired@example.test', 'invited_by_user_id' => $administrator->id]);
    UserInvitation::factory()->revoked()->create(['email' => 'revoked@example.test', 'invited_by_user_id' => $administrator->id]);

    $this->actingAs($administrator)
        ->get(route('admin.invitations.index'))
        ->assertOk()
        ->assertSee('Pending')
        ->assertSee('Accepted')
        ->assertSee('Expired')
        ->assertSee('Revoked')
        ->assertSee('Send new link')
        ->assertDontSee($rawToken);
});

test('administrator can rotate an expired invitation into a new usable link', function () {
    Mail::fake();
    $administrator = User::factory()->platformAdmin()->create();
    $invitation = UserInvitation::factory()->expired()->create([
        'invited_by_user_id' => $administrator->id,
        'email' => 'rotate@example.test',
    ]);
    $oldTokenHash = $invitation->token_hash;

    Livewire::actingAs($administrator)->test(InvitationIndex::class)
        ->call('resend', $invitation->id)
        ->assertHasNoErrors();

    expect($invitation->refresh()->isPending())->toBeTrue()
        ->and($invitation->token_hash)->not->toBe($oldTokenHash);
    Mail::assertSent(UserInvitationMail::class);
});

test('suspended platform administrator is denied administration routes', function () {
    $administrator = User::factory()->platformAdmin()->suspended()->create();

    $this->actingAs($administrator)
        ->get(route('admin.dashboard'))
        ->assertForbidden();
});

test('administrator seeding never overwrites an existing password', function () {
    $administrator = User::factory()->platformAdmin()->create([
        'email' => 'owner@example.test',
        'password' => 'existing-password',
        'status' => UserStatus::Suspended,
    ]);
    config()->set('platform.administrator', [
        'name' => 'Configured Owner',
        'email' => ' OWNER@EXAMPLE.TEST ',
        'password' => 'replacement-password',
    ]);

    $this->seed(PlatformAdministratorSeeder::class);

    expect($administrator->refresh()->name)->toBe('Configured Owner')
        ->and($administrator->status)->toBe(UserStatus::Active)
        ->and(Hash::check('existing-password', $administrator->password))->toBeTrue()
        ->and(Hash::check('replacement-password', $administrator->password))->toBeFalse();
});

test('development samples use a real complete official draw and remain idempotent', function () {
    User::factory()->platformAdmin()->create();

    $this->seed(DevelopmentSampleSeeder::class);
    $this->seed(DevelopmentSampleSeeder::class);

    $completed = Lottery::query()->where('name', 'Last Season Official Lottery')->firstOrFail();
    $draw = $completed->officialDraw()->firstOrFail();

    expect(Lottery::query()->whereIn('name', [
        'Keeper League Official Lottery',
        'Keeper League Simulation',
        'Last Season Official Lottery',
        'Archived Practice Lottery',
    ])->count())->toBe(4)
        ->and(LotteryDraw::query()->where('lottery_id', $completed->id)->count())->toBe(1)
        ->and(LotteryDrawResult::query()->where('lottery_draw_id', $draw->id)->count())->toBe(4)
        ->and($draw->results()->pluck('selection_position')->all())->toBe([1, 2, 3, 4])
        ->and($draw->results()->where('selection_position', 1)->firstOrFail()->team_snapshot['weighted_winner'])->toBeTrue();
});
