<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Actions\Lotteries\RunOfficialLotteryDraw;
use App\Enums\LotteryFormat;
use App\Enums\LotteryMode;
use App\Enums\LotteryStatus;
use App\Models\Lottery;
use App\Models\User;
use Illuminate\Database\Seeder;

final class DevelopmentSampleSeeder extends Seeder
{
    /** @var list<array{name: string, abbreviation: string, weight: int}> */
    private const TEAMS = [
        ['name' => 'Toronto Wolves', 'abbreviation' => 'TOR', 'weight' => 4000],
        ['name' => 'Montreal Bears', 'abbreviation' => 'MTL', 'weight' => 3000],
        ['name' => 'Ottawa Raptors', 'abbreviation' => 'OTT', 'weight' => 2000],
        ['name' => 'Halifax Sharks', 'abbreviation' => 'HAL', 'weight' => 1000],
    ];

    public function run(): void
    {
        if (app()->isProduction()) {
            $this->command->warn('Development sample data is disabled in production.');

            return;
        }

        $owner = User::query()
            ->where('is_platform_admin', true)
            ->where('status', 'active')
            ->first();

        if (! $owner instanceof User) {
            $this->command->warn('Development samples were not seeded; seed an active platform administrator first.');

            return;
        }

        $this->sampleLottery($owner, 'Keeper League Official Lottery', LotteryMode::Official, LotteryStatus::Ready);
        $this->sampleLottery($owner, 'Keeper League Simulation', LotteryMode::Simulation, LotteryStatus::Ready);

        $completed = $this->sampleLottery($owner, 'Last Season Official Lottery', LotteryMode::Official, LotteryStatus::Ready);
        if (! $completed->officialDraw()->exists()) {
            app(RunOfficialLotteryDraw::class)->handle($owner, $completed);
        }

        $this->sampleLottery($owner, 'Archived Practice Lottery', LotteryMode::Simulation, LotteryStatus::Archived);
    }

    private function sampleLottery(User $owner, string $name, LotteryMode $mode, LotteryStatus $status): Lottery
    {
        $lottery = $owner->lotteries()->firstOrCreate(
            ['name' => $name],
            [
                'mode' => $mode,
                'format' => LotteryFormat::WeightedSinglePick,
                'status' => $status,
                'archived_at' => $status === LotteryStatus::Archived ? now() : null,
            ],
        );

        if ($lottery->teams()->doesntExist()) {
            foreach (self::TEAMS as $index => $team) {
                $lottery->teams()->create([...$team, 'display_order' => $index + 1]);
            }
        }

        return $lottery;
    }
}
