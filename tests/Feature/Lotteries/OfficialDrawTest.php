<?php

use App\Actions\Lotteries\RunOfficialLotteryDraw;
use App\Actions\Lotteries\UpdateLottery;
use App\Enums\LotteryMode;
use App\Enums\LotteryStatus;
use App\Exceptions\LotteryCannotBeRun;
use App\Exceptions\OfficialDrawAlreadyCompleted;
use App\Models\LotteryDraw;
use App\Models\LotteryDrawResult;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Validation\ValidationException;

test('owner can persist a complete authoritative official draft order', function () {
    useRandomNumbers([8000]);
    $owner = User::factory()->create();
    $lottery = configuredLottery($owner);

    $result = app(RunOfficialLotteryDraw::class)->handle($owner, $lottery);
    $draw = LotteryDraw::query()->firstOrFail();

    expect($result->official)->toBeTrue()
        ->and($result->source)->toBe('official')
        ->and(array_map(fn ($position) => $position->teamName, $result->positions))->toBe(['Raptors', 'Wolves', 'Bears'])
        ->and($draw->results)->toHaveCount(3)
        ->and($draw->results->pluck('selection_position')->all())->toBe([1, 2, 3])
        ->and($draw->results->first()->team_snapshot['weighted_winner'])->toBeTrue()
        ->and($draw->results->every(fn ($stored) => $stored->team_snapshot !== []))->toBeTrue()
        ->and($draw->initiated_by_user_id)->toBe($owner->id)
        ->and($draw->completed_at)->not->toBeNull()
        ->and($lottery->refresh()->status)->toBe(LotteryStatus::Completed);
});

test('official draw stores a complete auditable configuration snapshot', function () {
    useRandomNumbers([1]);
    $owner = User::factory()->create();
    $lottery = configuredLottery($owner);

    app(RunOfficialLotteryDraw::class)->handle($owner, $lottery);
    $snapshot = $lottery->officialDraw()->firstOrFail()->configuration_snapshot;

    expect($snapshot['lottery_id'])->toBe($lottery->id)
        ->and($snapshot['lottery_name'])->toBe($lottery->name)
        ->and($snapshot['mode'])->toBe('official')
        ->and($snapshot['format'])->toBe('weighted_single_pick')
        ->and($snapshot['algorithm_version'])->toBe('weighted-single-pick-v1')
        ->and($snapshot['total_weight'])->toBe(10_000)
        ->and($snapshot['teams'])->toHaveCount(3)
        ->and(array_column($snapshot['teams'], 'weight'))->toBe([4000, 3500, 2500])
        ->and(array_column($snapshot['teams'], 'display_order'))->toBe([1, 2, 3]);
});

test('completed official lottery cannot be edited or drawn again', function () {
    useRandomNumbers([1]);
    $owner = User::factory()->create();
    $lottery = configuredLottery($owner);
    $action = app(RunOfficialLotteryDraw::class);

    $action->handle($owner, $lottery);

    expect(fn () => $action->handle($owner, $lottery->refresh()))
        ->toThrow(OfficialDrawAlreadyCompleted::class)
        ->and(fn () => app(UpdateLottery::class)->handle($owner, $lottery->refresh(), lotteryData()))
        ->toThrow(AuthorizationException::class)
        ->and(LotteryDraw::query()->count())->toBe(1)
        ->and(LotteryDrawResult::query()->count())->toBe(3);
});

test('official draw rejects invalid modes statuses archives and configurations', function (LotteryMode $mode, LotteryStatus $status, array $weights, string $exception) {
    useRandomNumbers([1]);
    $owner = User::factory()->create();
    $lottery = configuredLottery($owner, $mode, $status, $weights);

    expect(fn () => app(RunOfficialLotteryDraw::class)->handle($owner, $lottery))
        ->toThrow($exception)
        ->and(LotteryDraw::query()->count())->toBe(0);
})->with([
    'draft official' => [LotteryMode::Official, LotteryStatus::Draft, [4000, 3500, 2500], LotteryCannotBeRun::class],
    'simulation mode' => [LotteryMode::Simulation, LotteryStatus::Ready, [4000, 3500, 2500], LotteryCannotBeRun::class],
    'archived official' => [LotteryMode::Official, LotteryStatus::Archived, [4000, 3500, 2500], LotteryCannotBeRun::class],
    'invalid database configuration' => [LotteryMode::Official, LotteryStatus::Ready, [4000, 3500, 2499], ValidationException::class],
]);

test('users and platform administrators cannot draw another users lottery', function (bool $administrator) {
    useRandomNumbers([1]);
    $owner = User::factory()->create();
    $actor = User::factory()->create(['is_platform_admin' => $administrator]);
    $lottery = configuredLottery($owner);

    expect(fn () => app(RunOfficialLotteryDraw::class)->handle($actor, $lottery))
        ->toThrow(AuthorizationException::class)
        ->and(LotteryDraw::query()->count())->toBe(0);
})->with([false, true]);

test('official action always draws from authoritative database configuration', function () {
    useRandomNumbers([4001]);
    $owner = User::factory()->create();
    $lottery = configuredLottery($owner, weights: [4000, 3500, 2500]);

    $result = app(RunOfficialLotteryDraw::class)->handle($owner, $lottery);

    expect($result->winner()->teamName)->toBe('Bears')
        ->and($result->winner()->weight)->toBe(3500);
});
