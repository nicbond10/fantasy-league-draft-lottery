<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Lottery;
use App\Models\LotteryTeam;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<LotteryTeam> */
final class LotteryTeamFactory extends Factory
{
    protected $model = LotteryTeam::class;

    /** @return array<string, mixed> */
    public function definition(): array
    {
        return [
            'lottery_id' => Lottery::factory(),
            'name' => fake()->unique()->city().' '.fake()->randomElement(['Wolves', 'Bears', 'Raptors', 'Sharks']),
            'abbreviation' => fake()->optional()->lexify('???'),
            'weight' => 2500,
            'display_order' => 1,
        ];
    }
}
