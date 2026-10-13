<?php

use App\Actions\Lotteries\UpdateLottery;
use App\Enums\LotteryPresentationMode;
use App\Enums\LotteryStatus;
use App\Models\Lottery;
use App\Models\LotteryTeam;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Validation\ValidationException;

test('an owner can update draft and ready lotteries', function () {
    $owner = User::factory()->create();
    $lottery = Lottery::factory()->for($owner, 'owner')->create();
    $teams = [lotteryTeamData('Wolves', 5500, 1), lotteryTeamData('Bears', 4500, 2)];

    app(UpdateLottery::class)->handle($owner, $lottery, lotteryData(LotteryStatus::Ready, $teams, name: 'Updated Lottery'));

    expect($lottery->refresh()->name)->toBe('Updated Lottery')
        ->and($lottery->status)->toBe(LotteryStatus::Ready);

    app(UpdateLottery::class)->handle($owner, $lottery, lotteryData(LotteryStatus::Draft, [], name: 'Draft Again'));

    expect($lottery->refresh()->status)->toBe(LotteryStatus::Draft)
        ->and($lottery->teams()->count())->toBe(0);
});

test('saving invalid edits as ready is rejected', function () {
    $owner = User::factory()->create();
    $lottery = Lottery::factory()->for($owner, 'owner')->ready()->create();

    expect(fn () => app(UpdateLottery::class)->handle(
        $owner,
        $lottery,
        lotteryData(LotteryStatus::Ready, [lotteryTeamData('Wolves', 5000, 1)]),
    ))->toThrow(ValidationException::class);

    expect($lottery->refresh()->status)->toBe(LotteryStatus::Ready);
});

test('reordering and removing teams persists contiguous positions', function () {
    $owner = User::factory()->create();
    $lottery = Lottery::factory()->for($owner, 'owner')->create();
    LotteryTeam::factory()->for($lottery)->create(['name' => 'Wolves', 'display_order' => 1]);
    LotteryTeam::factory()->for($lottery)->create(['name' => 'Bears', 'display_order' => 2]);
    LotteryTeam::factory()->for($lottery)->create(['name' => 'Raptors', 'display_order' => 3]);

    $teams = [lotteryTeamData('Raptors', 6000, 1), lotteryTeamData('Wolves', 4000, 2)];
    app(UpdateLottery::class)->handle($owner, $lottery, lotteryData(LotteryStatus::Ready, $teams));

    expect($lottery->teams()->pluck('name')->all())->toBe(['Raptors', 'Wolves'])
        ->and($lottery->teams()->pluck('display_order')->all())->toBe([1, 2]);
});

test('archived lotteries cannot be updated', function () {
    $owner = User::factory()->create();
    $lottery = Lottery::factory()->for($owner, 'owner')->archived()->create();

    expect(fn () => app(UpdateLottery::class)->handle($owner, $lottery, lotteryData()))
        ->toThrow(AuthorizationException::class);
});

test('an editable lottery can change its official presentation mode', function () {
    $owner = User::factory()->create();
    $lottery = Lottery::factory()->for($owner, 'owner')->create();

    app(UpdateLottery::class)->handle(
        $owner,
        $lottery,
        lotteryData(presentationMode: LotteryPresentationMode::Autoplay),
    );

    expect($lottery->refresh()->presentation_mode)->toBe(LotteryPresentationMode::Autoplay);
});
