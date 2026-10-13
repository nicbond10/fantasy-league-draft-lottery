<?php

use App\Actions\Lotteries\ArchiveLottery;
use App\Actions\Lotteries\UpdateLottery;
use App\Enums\LotteryStatus;
use App\Models\Lottery;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;

test('an owner can archive a lottery without deleting it or its teams', function () {
    $owner = User::factory()->create();
    $lottery = Lottery::factory()->for($owner, 'owner')->create();
    $lottery->teams()->create([
        'name' => 'Wolves',
        'abbreviation' => 'WOL',
        'weight' => 10_000,
        'display_order' => 1,
    ]);

    app(ArchiveLottery::class)->handle($owner, $lottery);

    expect($lottery->refresh()->status)->toBe(LotteryStatus::Archived)
        ->and($lottery->archived_at)->not->toBeNull()
        ->and($lottery->teams()->count())->toBe(1);
});

test('an archived lottery remains viewable by its owner but cannot be edited', function () {
    $owner = User::factory()->create();
    $lottery = Lottery::factory()->for($owner, 'owner')->archived()->create();

    $this->actingAs($owner)
        ->get(route('lotteries.show', ['lotteryId' => $lottery->id]))
        ->assertOk();

    $this->get(route('lotteries.edit', ['lotteryId' => $lottery->id]))
        ->assertForbidden();

    expect(fn () => app(UpdateLottery::class)->handle($owner, $lottery, lotteryData()))
        ->toThrow(AuthorizationException::class);
});

test('no user-facing hard delete route is exposed', function () {
    $owner = User::factory()->create();
    $lottery = Lottery::factory()->for($owner, 'owner')->create();

    $this->actingAs($owner)
        ->delete('/lotteries/'.$lottery->id)
        ->assertMethodNotAllowed();

    expect($lottery->fresh())->not->toBeNull();
});
