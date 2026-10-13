<?php

use App\Models\Lottery;
use App\Models\LotteryTeam;
use App\Models\User;

test('a lottery belongs to its owner', function () {
    $owner = User::factory()->create();
    $lottery = Lottery::factory()->for($owner, 'owner')->create();

    expect($lottery->owner->is($owner))->toBeTrue()
        ->and($owner->lotteries()->first()->is($lottery))->toBeTrue();
});

test('lottery teams are always returned in display order', function () {
    $lottery = Lottery::factory()->create();
    LotteryTeam::factory()->for($lottery)->create(['name' => 'Sharks', 'display_order' => 2]);
    LotteryTeam::factory()->for($lottery)->create(['name' => 'Wolves', 'display_order' => 1]);

    expect($lottery->teams()->pluck('name')->all())->toBe(['Wolves', 'Sharks']);
});
