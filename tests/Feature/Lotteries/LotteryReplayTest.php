<?php

use App\Actions\Lotteries\ArchiveLottery;
use App\Actions\Lotteries\GetLotteryReplay;
use App\Actions\Lotteries\RunOfficialLotteryDraw;
use App\Contracts\RandomNumberGenerator;
use App\Enums\LotteryMode;
use App\Enums\LotteryStatus;
use App\Exceptions\LotteryCannotBeRun;
use App\Models\LotteryDraw;
use App\Models\LotteryDrawResult;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;

test('owner replay returns the exact complete stored order without randomness or writes', function () {
    useRandomNumbers([8000]);
    $owner = User::factory()->create();
    $lottery = configuredLottery($owner);
    $official = app(RunOfficialLotteryDraw::class)->handle($owner, $lottery);
    $drawUpdatedAt = $lottery->officialDraw()->firstOrFail()->updated_at;
    $resultCount = LotteryDrawResult::query()->count();

    app()->instance(RandomNumberGenerator::class, new class implements RandomNumberGenerator
    {
        public function between(int $minimum, int $maximum): int
        {
            throw new RuntimeException('Replay attempted to use randomness.');
        }
    });

    $replay = app(GetLotteryReplay::class)->handle($owner, $lottery->refresh());

    expect($replay->source)->toBe('replay')
        ->and($replay->official)->toBeTrue()
        ->and(array_map(fn ($position) => $position->toArray(), $replay->positions))
        ->toBe(array_map(fn ($position) => $position->toArray(), $official->positions))
        ->and($replay->initiatedBy)->toBe(['id' => $owner->id, 'name' => $owner->name])
        ->and(LotteryDraw::query()->firstOrFail()->updated_at->equalTo($drawUpdatedAt))->toBeTrue()
        ->and(LotteryDrawResult::query()->count())->toBe($resultCount);
});

test('replay remains authoritative after current team references are deleted', function () {
    useRandomNumbers([8000]);
    $owner = User::factory()->create();
    $lottery = configuredLottery($owner);
    app(RunOfficialLotteryDraw::class)->handle($owner, $lottery);

    $lottery->teams()->delete();
    $lottery->forceFill(['name' => 'Changed Current Name'])->save();

    $replay = app(GetLotteryReplay::class)->handle($owner, $lottery->refresh());

    expect($replay->lotteryName)->not->toBe('Changed Current Name')
        ->and(array_map(fn ($position) => $position->teamName, $replay->positions))->toBe(['Raptors', 'Wolves', 'Bears'])
        ->and($lottery->officialDraw()->firstOrFail()->results()->whereNotNull('lottery_team_id')->count())->toBe(0);
});

test('replay rejects other users and lotteries without official results', function () {
    $owner = User::factory()->create();
    $other = User::factory()->create();
    $simulation = configuredLottery($owner, LotteryMode::Simulation);
    $officialReady = configuredLottery($owner);

    expect(fn () => app(GetLotteryReplay::class)->handle($other, $officialReady))
        ->toThrow(AuthorizationException::class)
        ->and(fn () => app(GetLotteryReplay::class)->handle($owner, $simulation))
        ->toThrow(LotteryCannotBeRun::class)
        ->and(fn () => app(GetLotteryReplay::class)->handle($owner, $officialReady))
        ->toThrow(LotteryCannotBeRun::class);
});

test('archived completed official result remains retrievable as view data', function () {
    useRandomNumbers([1]);
    $owner = User::factory()->create();
    $lottery = configuredLottery($owner);
    app(RunOfficialLotteryDraw::class)->handle($owner, $lottery);
    app(ArchiveLottery::class)->handle($owner, $lottery->refresh());

    $replay = app(GetLotteryReplay::class)->handle($owner, $lottery->refresh());

    expect($lottery->status)->toBe(LotteryStatus::Archived)
        ->and($replay->positions)->toHaveCount(3)
        ->and($replay->winner()->teamName)->toBe('Wolves');
});
