<?php

declare(strict_types=1);

namespace App\Data;

use App\Support\Percentage;

final readonly class DraftPositionData
{
    public function __construct(
        public int $finalPosition,
        public ?int $teamId,
        public string $teamName,
        public ?string $abbreviation,
        public int $originalDisplayOrder,
        public int $weight,
        public bool $winner,
    ) {}

    /** @return array{final_position: int, team_id: int|null, team_name: string, abbreviation: string|null, original_display_order: int, weight: int, percentage: string, winner: bool} */
    public function toArray(): array
    {
        return [
            'final_position' => $this->finalPosition,
            'team_id' => $this->teamId,
            'team_name' => $this->teamName,
            'abbreviation' => $this->abbreviation,
            'original_display_order' => $this->originalDisplayOrder,
            'weight' => $this->weight,
            'percentage' => Percentage::fromBasisPoints($this->weight),
            'winner' => $this->winner,
        ];
    }

    /** @return array{team_id: int|null, name: string, abbreviation: string|null, weight: int, original_display_order: int, final_selection_position: int, weighted_winner: bool} */
    public function toSnapshot(): array
    {
        return [
            'team_id' => $this->teamId,
            'name' => $this->teamName,
            'abbreviation' => $this->abbreviation,
            'weight' => $this->weight,
            'original_display_order' => $this->originalDisplayOrder,
            'final_selection_position' => $this->finalPosition,
            'weighted_winner' => $this->winner,
        ];
    }
}
