<?php

declare(strict_types=1);

namespace App\Actions\Lotteries;

use App\Data\DraftOrderResultData;
use App\Data\LotteryData;
use App\Enums\LotteryMode;
use App\Enums\LotteryStatus;
use App\Exceptions\LotteryCannotBeRun;
use App\Exceptions\OfficialDrawAlreadyCompleted;
use App\Models\Lottery;
use App\Models\LotteryDraw;
use App\Models\User;
use App\Services\LotteryConfigurationFactory;
use App\Services\LotteryConfigurationValidator;
use App\Services\WeightedSinglePickDraw;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;

final readonly class RunOfficialLotteryDraw
{
    public function __construct(
        private LotteryConfigurationFactory $configurationFactory,
        private LotteryConfigurationValidator $validator,
        private WeightedSinglePickDraw $drawService,
    ) {}

    public function handle(User $actor, Lottery $lottery): DraftOrderResultData
    {
        Gate::forUser($actor)->authorize('view', $lottery);
        $this->assertRunnable($lottery);
        Gate::forUser($actor)->authorize('runOfficialDraw', $lottery);

        try {
            $result = DB::transaction(function () use ($actor, $lottery): DraftOrderResultData {
                $lockedLottery = Lottery::query()->lockForUpdate()->findOrFail($lottery->id);

                Gate::forUser($actor)->authorize('view', $lockedLottery);
                $this->assertRunnable($lockedLottery);
                Gate::forUser($actor)->authorize('runOfficialDraw', $lockedLottery);

                $configuration = $this->configurationFactory->fromLottery($lockedLottery);
                $this->validator->validate($configuration);

                $completedAt = now();
                $result = $this->drawService
                    ->draw($lockedLottery->id, $configuration, official: true)
                    ->asOfficial($completedAt, ['id' => $actor->id, 'name' => $actor->name]);

                $draw = $lockedLottery->officialDraw()->create([
                    'initiated_by_user_id' => $actor->id,
                    'algorithm_version' => WeightedSinglePickDraw::ALGORITHM_VERSION,
                    'configuration_snapshot' => $this->configurationSnapshot($lockedLottery, $configuration),
                    'completed_at' => $completedAt,
                ]);

                foreach ($result->positions as $position) {
                    $draw->results()->create([
                        'lottery_team_id' => $position->teamId,
                        'selection_position' => $position->finalPosition,
                        'team_snapshot' => $position->toSnapshot(),
                    ]);
                }

                $lockedLottery->update(['status' => LotteryStatus::Completed]);

                return $result;
            }, attempts: 3);

            Log::notice('Official lottery draw completed.', [
                'lottery_id' => $lottery->id,
                'initiated_by_user_id' => $actor->id,
                'position_count' => count($result->positions),
            ]);

            return $result;
        } catch (QueryException $exception) {
            if (LotteryDraw::query()->where('lottery_id', $lottery->id)->exists()) {
                Log::warning('Duplicate official lottery draw rejected.', [
                    'lottery_id' => $lottery->id,
                    'initiated_by_user_id' => $actor->id,
                ]);

                throw new OfficialDrawAlreadyCompleted('This official lottery has already been drawn.', previous: $exception);
            }

            throw $exception;
        }
    }

    private function assertRunnable(Lottery $lottery): void
    {
        if ($lottery->officialDraw()->exists() || $lottery->status === LotteryStatus::Completed) {
            throw new OfficialDrawAlreadyCompleted('This official lottery has already been drawn.');
        }

        if ($lottery->mode !== LotteryMode::Official || $lottery->status !== LotteryStatus::Ready) {
            throw new LotteryCannotBeRun('Only a ready official lottery can run an official draw.');
        }
    }

    /** @return array<string, mixed> */
    private function configurationSnapshot(Lottery $lottery, LotteryData $configuration): array
    {
        return [
            'lottery_id' => $lottery->id,
            'lottery_name' => $configuration->name,
            'mode' => $configuration->mode->value,
            'format' => $configuration->format->value,
            'algorithm_version' => WeightedSinglePickDraw::ALGORITHM_VERSION,
            'total_weight' => array_sum(array_map(static fn ($team): int => $team->weight, $configuration->teams)),
            'teams' => array_map(static fn ($team): array => [
                'team_id' => $team->id,
                'name' => $team->name,
                'abbreviation' => $team->abbreviation,
                'weight' => $team->weight,
                'display_order' => $team->displayOrder,
            ], $configuration->teams),
        ];
    }
}
