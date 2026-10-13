<?php

use App\Actions\Lotteries\CreateLottery;
use App\Enums\LotteryPresentationMode;
use App\Enums\LotteryStatus;
use App\Models\Lottery;
use App\Models\User;
use Illuminate\Validation\ValidationException;

test('a user can create an incomplete draft lottery', function () {
    $owner = User::factory()->create();

    $lottery = app(CreateLottery::class)->handle($owner, lotteryData());

    expect($lottery->status)->toBe(LotteryStatus::Draft)
        ->and($lottery->teams)->toHaveCount(0)
        ->and($lottery->owner->is($owner))->toBeTrue();
});

test('a user can create a valid ready lottery with normalized teams', function () {
    $owner = User::factory()->create();
    $teams = [
        lotteryTeamData(' Wolves ', 6000, 1, 'wol'),
        lotteryTeamData('Bears', 4000, 2, ' brs '),
    ];

    $lottery = app(CreateLottery::class)->handle($owner, lotteryData(LotteryStatus::Ready, $teams));

    expect($lottery->status)->toBe(LotteryStatus::Ready)
        ->and($lottery->teams->pluck('name')->all())->toBe(['Wolves', 'Bears'])
        ->and($lottery->teams->pluck('abbreviation')->all())->toBe(['WOL', 'BRS'])
        ->and($lottery->teams->pluck('display_order')->all())->toBe([1, 2]);
});

test('a ready lottery requires at least two teams', function () {
    $owner = User::factory()->create();

    expect(fn () => app(CreateLottery::class)->handle(
        $owner,
        lotteryData(LotteryStatus::Ready, [lotteryTeamData('Wolves', 10_000, 1)]),
    ))->toThrow(ValidationException::class);
});

test('a ready lottery requires exactly ten thousand basis points', function () {
    $owner = User::factory()->create();
    $teams = [lotteryTeamData('Wolves', 5000, 1), lotteryTeamData('Bears', 4999, 2)];

    expect(fn () => app(CreateLottery::class)->handle($owner, lotteryData(LotteryStatus::Ready, $teams)))
        ->toThrow(ValidationException::class);
});

test('duplicate normalized team names are rejected', function () {
    $owner = User::factory()->create();
    $teams = [lotteryTeamData('Wolves', 5000, 1), lotteryTeamData(' wolves ', 5000, 2)];

    expect(fn () => app(CreateLottery::class)->handle($owner, lotteryData(LotteryStatus::Draft, $teams)))
        ->toThrow(ValidationException::class);
});

test('a lottery owner always comes from the authenticated workflow user', function () {
    $owner = User::factory()->create();
    $otherUser = User::factory()->create();

    $lottery = app(CreateLottery::class)->handle($owner, lotteryData());

    expect($lottery->user_id)->toBe($owner->id)
        ->and($lottery->user_id)->not->toBe($otherUser->id)
        ->and(Lottery::query()->count())->toBe(1);
});

test('a lottery persists its configured official presentation mode', function () {
    $owner = User::factory()->create();

    $lottery = app(CreateLottery::class)->handle(
        $owner,
        lotteryData(presentationMode: LotteryPresentationMode::Autoplay),
    );

    expect($lottery->presentation_mode)->toBe(LotteryPresentationMode::Autoplay);
});
