<?php

declare(strict_types=1);

namespace App\Services;

use App\Contracts\RandomNumberGenerator;
use App\Data\DraftOrderResultData;
use App\Data\DraftPositionData;
use App\Data\LotteryData;
use App\Data\LotteryTeamData;
use InvalidArgumentException;

final readonly class WeightedSinglePickDraw
{
    public const ALGORITHM_VERSION = 'weighted-single-pick-v1';

    public function __construct(private RandomNumberGenerator $random) {}

    public function draw(int $lotteryId, LotteryData $configuration, bool $official = false): DraftOrderResultData
    {
        $teams = $configuration->teams;
        usort($teams, static fn (LotteryTeamData $left, LotteryTeamData $right): int => $left->displayOrder <=> $right->displayOrder);

        $this->assertValidConfiguration($teams);

        $ticket = $this->random->between(1, 10_000);
        $cumulativeWeight = 0;
        $winner = null;

        foreach ($teams as $team) {
            $cumulativeWeight += $team->weight;

            if ($ticket <= $cumulativeWeight) {
                $winner = $team;
                break;
            }
        }

        if (! $winner instanceof LotteryTeamData) {
            throw new InvalidArgumentException('The weighted ticket did not map to a configured team.');
        }

        $finalTeams = [$winner];

        foreach ($teams as $team) {
            if ($team !== $winner) {
                $finalTeams[] = $team;
            }
        }

        $positions = [];

        foreach ($finalTeams as $index => $team) {
            $positions[] = new DraftPositionData(
                finalPosition: $index + 1,
                teamId: $team->id,
                teamName: $team->name,
                abbreviation: $team->abbreviation,
                originalDisplayOrder: $team->displayOrder,
                weight: $team->weight,
                winner: $team === $winner,
            );
        }

        return new DraftOrderResultData(
            lotteryId: $lotteryId,
            lotteryName: $configuration->name,
            mode: $configuration->mode,
            format: $configuration->format,
            algorithmVersion: self::ALGORITHM_VERSION,
            official: $official,
            source: $official ? 'official' : 'simulation',
            positions: $positions,
        );
    }

    /** @param list<LotteryTeamData> $teams */
    private function assertValidConfiguration(array $teams): void
    {
        if (count($teams) < 2) {
            throw new InvalidArgumentException('A weighted draw requires at least two teams.');
        }

        $total = 0;

        foreach ($teams as $index => $team) {
            if ($team->displayOrder !== $index + 1) {
                throw new InvalidArgumentException('Team display order must be contiguous.');
            }

            if ($team->weight <= 0) {
                throw new InvalidArgumentException('Every team must have a positive weight.');
            }

            $total += $team->weight;
        }

        if ($total !== 10_000) {
            throw new InvalidArgumentException('Team weights must total exactly 10,000 basis points.');
        }
    }
}
