<?php

use App\Actions\Lotteries\RunLotterySimulation;
use App\Enums\LotteryMode;
use App\Enums\LotteryStatus;
use App\Exceptions\LotteryCannotBeRun;
use App\Models\LotteryDraw;
use App\Models\LotteryDrawResult;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;

test('owner can repeatedly simulate complete transient draft orders', function () {
    useRandomNumbers([1, 8000]);
    $owner = User::factory()->create();
    $lottery = configuredLottery($owner, LotteryMode::Simulation);
    $action = app(RunLotterySimulation::class);

    $first = $action->handle($owner, $lottery);
    $second = $action->handle($owner, $lottery);

    expect(array_map(fn ($position) => $position->teamName, $first->positions))->toBe(['Wolves', 'Bears', 'Raptors'])
        ->and(array_map(fn ($position) => $position->teamName, $second->positions))->toBe(['Raptors', 'Wolves', 'Bears'])
        ->and($first->winner()->finalPosition)->toBe(1)
        ->and($second->winner()->finalPosition)->toBe(1)
        ->and($first->official)->toBeFalse()
        ->and($lottery->refresh()->status)->toBe(LotteryStatus::Ready)
        ->and(LotteryDraw::query()->count())->toBe(0)
        ->and(LotteryDrawResult::query()->count())->toBe(0);
});

test('simulation rejects official draft and archived lotteries', function (LotteryMode $mode, LotteryStatus $status) {
    useRandomNumbers([1]);
    $owner = User::factory()->create();
    $lottery = configuredLottery($owner, $mode, $status);

    expect(fn () => app(RunLotterySimulation::class)->handle($owner, $lottery))
        ->toThrow(LotteryCannotBeRun::class)
        ->and(LotteryDraw::query()->count())->toBe(0);
})->with([
    [LotteryMode::Official, LotteryStatus::Ready],
    [LotteryMode::Simulation, LotteryStatus::Draft],
    [LotteryMode::Simulation, LotteryStatus::Archived],
]);

test('a user cannot simulate another users lottery', function () {
    useRandomNumbers([1]);
    $owner = User::factory()->create();
    $actor = User::factory()->create();
    $lottery = configuredLottery($owner, LotteryMode::Simulation);

    expect(fn () => app(RunLotterySimulation::class)->handle($actor, $lottery))
        ->toThrow(AuthorizationException::class);
});
