<?php

use App\Actions\Lotteries\RunOfficialLotteryDraw;
use App\Models\LotteryDraw;
use App\Models\LotteryDrawResult;
use App\Models\User;
use Illuminate\Database\QueryException;

test('database permits only one official draw per lottery', function () {
    $owner = User::factory()->create();
    $lottery = configuredLottery($owner);
    $attributes = [
        'initiated_by_user_id' => $owner->id,
        'algorithm_version' => 'weighted-single-pick-v1',
        'configuration_snapshot' => [],
        'completed_at' => now(),
    ];

    $lottery->officialDraw()->create($attributes);

    expect(fn () => $lottery->officialDraw()->create($attributes))->toThrow(QueryException::class);
});

test('database enforces unique final positions and retained teams per draw', function () {
    useRandomNumbers([1]);
    $owner = User::factory()->create();
    $lottery = configuredLottery($owner);
    app(RunOfficialLotteryDraw::class)->handle($owner, $lottery);
    $draw = $lottery->officialDraw()->firstOrFail();
    $result = $draw->results()->firstOrFail();

    expect(fn () => LotteryDrawResult::factory()->create([
        'lottery_draw_id' => $draw->id,
        'selection_position' => $result->selection_position,
    ]))->toThrow(QueryException::class);

    expect(fn () => LotteryDrawResult::factory()->create([
        'lottery_draw_id' => $draw->id,
        'lottery_team_id' => $result->lottery_team_id,
        'selection_position' => 99,
    ]))->toThrow(QueryException::class);
});

test('nullable historical team references allow multiple snapshot-only result rows', function () {
    $draw = LotteryDraw::factory()->create();

    LotteryDrawResult::factory()->create([
        'lottery_draw_id' => $draw->id,
        'lottery_team_id' => null,
        'selection_position' => 1,
    ]);
    LotteryDrawResult::factory()->create([
        'lottery_draw_id' => $draw->id,
        'lottery_team_id' => null,
        'selection_position' => 2,
    ]);

    expect($draw->results()->count())->toBe(2);
});
