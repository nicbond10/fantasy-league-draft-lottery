<?php

declare(strict_types=1);

namespace App\Services;

use App\Data\LotteryData;
use App\Data\LotteryTeamData;
use App\Models\Lottery;

final class LotteryConfigurationFactory
{
    public function fromLottery(Lottery $lottery): LotteryData
    {
        $teams = [];

        foreach ($lottery->teams()->get() as $team) {
            $teams[] = new LotteryTeamData(
                name: $team->name,
                abbreviation: $team->abbreviation,
                weight: $team->weight,
                displayOrder: $team->display_order,
                id: $team->id,
            );
        }

        return new LotteryData(
            name: $lottery->name,
            mode: $lottery->mode,
            format: $lottery->format,
            status: $lottery->status,
            teams: $teams,
            presentationMode: $lottery->presentation_mode,
        );
    }
}
