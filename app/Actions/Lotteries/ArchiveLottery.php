<?php

declare(strict_types=1);

namespace App\Actions\Lotteries;

use App\Enums\LotteryStatus;
use App\Models\Lottery;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

final class ArchiveLottery
{
    public function handle(User $owner, Lottery $lottery): Lottery
    {
        Gate::forUser($owner)->authorize('archive', $lottery);

        return DB::transaction(function () use ($owner, $lottery): Lottery {
            $lockedLottery = Lottery::query()->lockForUpdate()->findOrFail($lottery->id);
            Gate::forUser($owner)->authorize('archive', $lockedLottery);

            $lockedLottery->update([
                'status' => LotteryStatus::Archived,
                'archived_at' => now(),
            ]);

            return $lockedLottery;
        });
    }
}
