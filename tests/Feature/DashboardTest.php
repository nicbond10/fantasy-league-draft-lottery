<?php

use App\Enums\LotteryMode;
use App\Enums\LotteryStatus;
use App\Models\Lottery;
use App\Models\LotteryDraw;
use App\Models\User;

test('guests are redirected to the login page', function () {
    $response = $this->get(route('dashboard'));
    $response->assertRedirect(route('login'));
});

test('authenticated users can visit the dashboard', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $response = $this->get(route('dashboard'));
    $response->assertOk();
});

test('dashboard summaries and recent lotteries are scoped to their owner', function () {
    $user = User::factory()->create();
    $otherUser = User::factory()->create();
    $active = Lottery::factory()->for($user, 'owner')->create(['name' => 'My Active Lottery']);
    Lottery::factory()->for($user, 'owner')->create([
        'name' => 'My Simulation',
        'mode' => LotteryMode::Simulation,
    ]);
    $completed = Lottery::factory()->for($user, 'owner')->completed()->create(['name' => 'My Completed Lottery']);
    LotteryDraw::factory()->for($completed)->create();
    Lottery::factory()->for($user, 'owner')->create([
        'name' => 'My Archived Lottery',
        'status' => LotteryStatus::Archived,
        'archived_at' => now(),
    ]);
    Lottery::factory()->for($otherUser, 'owner')->create(['name' => 'Someone Else Lottery']);

    $this->actingAs($user)
        ->get(route('dashboard'))
        ->assertOk()
        ->assertSee('Active lotteries')
        ->assertSee('Completed official')
        ->assertSee('Simulation lotteries')
        ->assertSee('My Active Lottery')
        ->assertSee('My Completed Lottery')
        ->assertDontSee('Someone Else Lottery');

    expect($active->owner->is($user))->toBeTrue();
});
