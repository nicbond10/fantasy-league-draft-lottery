<?php

declare(strict_types=1);

namespace App\Actions\Lotteries;

use App\Data\DraftOrderResultData;
use App\Data\DraftPositionData;
use App\Enums\LotteryFormat;
use App\Enums\LotteryMode;
use App\Exceptions\LotteryCannotBeRun;
use App\Models\Lottery;
use App\Models\User;
use Illuminate\Support\Facades\Gate;

final class GetLotteryReplay
{
    public function handle(User $actor, Lottery $lottery): DraftOrderResultData
    {
        Gate::forUser($actor)->authorize('view', $lottery);

        $draw = $lottery->officialDraw()->with(['results', 'initiatedBy'])->first();

        if ($draw === null) {
            throw new LotteryCannotBeRun('No saved official result is available for replay.');
        }

        Gate::forUser($actor)->authorize('replay', $lottery);

        $configuration = $draw->configuration_snapshot;
        $positions = [];

        foreach ($draw->results as $storedResult) {
            $snapshot = $storedResult->team_snapshot;
            $positions[] = new DraftPositionData(
                finalPosition: (int) $storedResult->selection_position,
                teamId: isset($snapshot['team_id']) ? (int) $snapshot['team_id'] : null,
                teamName: (string) $snapshot['name'],
                abbreviation: isset($snapshot['abbreviation']) ? (string) $snapshot['abbreviation'] : null,
                originalDisplayOrder: (int) $snapshot['original_display_order'],
                weight: (int) $snapshot['weight'],
                winner: (bool) $snapshot['weighted_winner'],
            );
        }

        return new DraftOrderResultData(
            lotteryId: (int) $configuration['lottery_id'],
            lotteryName: (string) $configuration['lottery_name'],
            mode: LotteryMode::from((string) $configuration['mode']),
            format: LotteryFormat::from((string) $configuration['format']),
            algorithmVersion: $draw->algorithm_version,
            official: true,
            source: 'replay',
            positions: $positions,
            completedAt: $draw->completed_at,
            initiatedBy: ['id' => $draw->initiatedBy->id, 'name' => $draw->initiatedBy->name],
        );
    }
}
