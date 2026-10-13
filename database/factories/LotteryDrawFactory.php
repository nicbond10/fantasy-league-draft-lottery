<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\LotteryMode;
use App\Models\Lottery;
use App\Models\LotteryDraw;
use App\Models\User;
use App\Services\WeightedSinglePickDraw;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<LotteryDraw> */
final class LotteryDrawFactory extends Factory
{
    protected $model = LotteryDraw::class;

    /** @return array<string, mixed> */
    public function definition(): array
    {
        return [
            'lottery_id' => Lottery::factory()->completed(),
            'initiated_by_user_id' => User::factory(),
            'algorithm_version' => WeightedSinglePickDraw::ALGORITHM_VERSION,
            'configuration_snapshot' => [
                'lottery_id' => 1,
                'lottery_name' => 'Saved Lottery',
                'mode' => LotteryMode::Official->value,
                'format' => 'weighted_single_pick',
                'algorithm_version' => WeightedSinglePickDraw::ALGORITHM_VERSION,
                'total_weight' => 10_000,
                'teams' => [],
            ],
            'completed_at' => now(),
        ];
    }
}
