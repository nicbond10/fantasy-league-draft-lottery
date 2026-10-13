<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\LotteryDraw;
use App\Models\LotteryDrawResult;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<LotteryDrawResult> */
final class LotteryDrawResultFactory extends Factory
{
    protected $model = LotteryDrawResult::class;

    /** @return array<string, mixed> */
    public function definition(): array
    {
        return [
            'lottery_draw_id' => LotteryDraw::factory(),
            'lottery_team_id' => null,
            'selection_position' => 1,
            'team_snapshot' => [
                'team_id' => null,
                'name' => fake()->city().' Wolves',
                'abbreviation' => 'WOL',
                'weight' => 10_000,
                'original_display_order' => 1,
                'final_selection_position' => 1,
                'weighted_winner' => true,
            ],
        ];
    }
}
