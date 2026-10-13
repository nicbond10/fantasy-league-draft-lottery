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

final readonly class CreateLottery
{
    public function __construct(private LotteryConfigurationValidator $validator) {}

    public function handle(User $owner, LotteryData $data): Lottery
    {
        Gate::forUser($owner)->authorize('create', Lottery::class);
        $this->validator->validate($data);

        return DB::transaction(function () use ($owner, $data): Lottery {
            $lottery = $owner->lotteries()->create([
                'name' => trim($data->name),
                'mode' => $data->mode,
                'format' => $data->format,
                'presentation_mode' => $data->presentationMode,
                'status' => $data->status,
            ]);

            foreach ($data->teams as $position => $team) {
                $lottery->teams()->create([
                    'name' => trim($team->name),
                    'abbreviation' => filled($team->abbreviation) ? Str::upper(trim($team->abbreviation)) : null,
                    'weight' => $team->weight,
                    'display_order' => $position + 1,
                ]);
            }

            return $lottery->load('teams');
        });
    }
}
