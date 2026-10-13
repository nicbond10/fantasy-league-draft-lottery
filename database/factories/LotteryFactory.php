<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\LotteryFormat;
use App\Enums\LotteryMode;
use App\Enums\LotteryPresentationMode;
use App\Enums\LotteryStatus;
use App\Models\Lottery;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Lottery> */
final class LotteryFactory extends Factory
{
    protected $model = Lottery::class;

    /** @return array<string, mixed> */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'name' => fake()->words(3, true),
            'mode' => LotteryMode::Official,
            'format' => LotteryFormat::WeightedSinglePick,
            'presentation_mode' => LotteryPresentationMode::Manual,
            'status' => LotteryStatus::Draft,
            'archived_at' => null,
        ];
    }

    public function ready(): static
    {
        return $this->state(fn (): array => ['status' => LotteryStatus::Ready]);
    }

    public function officialDraft(): static
    {
        return $this->state(fn (): array => [
            'mode' => LotteryMode::Official,
            'status' => LotteryStatus::Draft,
        ]);
    }

    public function officialReady(): static
    {
        return $this->state(fn (): array => [
            'mode' => LotteryMode::Official,
            'status' => LotteryStatus::Ready,
        ]);
    }

    public function simulationReady(): static
    {
        return $this->state(fn (): array => [
            'mode' => LotteryMode::Simulation,
            'status' => LotteryStatus::Ready,
        ]);
    }

    public function completed(): static
    {
        return $this->state(fn (): array => [
            'mode' => LotteryMode::Official,
            'status' => LotteryStatus::Completed,
        ]);
    }

    public function archived(): static
    {
        return $this->state(fn (): array => [
            'status' => LotteryStatus::Archived,
            'archived_at' => now(),
        ]);
    }

    public function simulation(): static
    {
        return $this->state(fn (): array => ['mode' => LotteryMode::Simulation]);
    }
}
