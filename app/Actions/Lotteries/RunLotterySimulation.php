<?php

declare(strict_types=1);

namespace App\Actions\Lotteries;

use App\Data\DraftOrderResultData;
use App\Enums\LotteryMode;
use App\Enums\LotteryStatus;
use App\Exceptions\LotteryCannotBeRun;
use App\Models\Lottery;
use App\Models\User;
use App\Services\LotteryConfigurationFactory;
use App\Services\LotteryConfigurationValidator;
use App\Services\WeightedSinglePickDraw;
use Illuminate\Support\Facades\Gate;

final readonly class RunLotterySimulation
{
    public function __construct(
        private LotteryConfigurationFactory $configurationFactory,
        private LotteryConfigurationValidator $validator,
        private WeightedSinglePickDraw $drawService,
    ) {}

    public function handle(User $actor, Lottery $lottery): DraftOrderResultData
    {
        Gate::forUser($actor)->authorize('view', $lottery);

        $authoritativeLottery = $actor->lotteries()->findOrFail($lottery->id);

        if ($authoritativeLottery->mode !== LotteryMode::Simulation || $authoritativeLottery->status !== LotteryStatus::Ready) {
            throw new LotteryCannotBeRun('Only a ready simulation lottery can be simulated.');
        }

        Gate::forUser($actor)->authorize('simulate', $authoritativeLottery);

        $configuration = $this->configurationFactory->fromLottery($authoritativeLottery);
        $this->validator->validate($configuration);

        return $this->drawService->draw($authoritativeLottery->id, $configuration);
    }
}
