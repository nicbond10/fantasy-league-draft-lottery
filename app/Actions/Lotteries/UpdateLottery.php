<?php

declare(strict_types=1);

namespace App\Actions\Lotteries;

use App\Data\LotteryData;
use App\Models\Lottery;
use App\Models\User;
use App\Services\LotteryConfigurationValidator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;

final readonly class UpdateLottery
{
    public function __construct(private LotteryConfigurationValidator $validator) {}

    public function handle(User $owner, Lottery $lottery, LotteryData $data): Lottery
    {
        Gate::forUser($owner)->authorize('update', $lottery);
        $this->validator->validate($data);

        return DB::transaction(function () use ($owner, $lottery, $data): Lottery {
            $lockedLottery = Lottery::query()->lockForUpdate()->findOrFail($lottery->id);
            Gate::forUser($owner)->authorize('update', $lockedLottery);

            $lockedLottery->update([
                'name' => trim($data->name),
                'mode' => $data->mode,
                'format' => $data->format,
                'presentation_mode' => $data->presentationMode,
                'status' => $data->status,
            ]);

            // Phase 3 has no historical draws. Replacing this small collection keeps
            // synchronization deterministic; completed lotteries become immutable in Phase 4.
            $lockedLottery->teams()->delete();

            foreach ($data->teams as $position => $team) {
                $lockedLottery->teams()->create([
                    'name' => trim($team->name),
                    'abbreviation' => filled($team->abbreviation) ? Str::upper(trim($team->abbreviation)) : null,
                    'weight' => $team->weight,
                    'display_order' => $position + 1,
                ]);
            }

            return $lockedLottery->load('teams');
        });
    }
}
