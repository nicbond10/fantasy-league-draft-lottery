<?php

use App\Contracts\RandomNumberGenerator;
use App\Data\LotteryData;
use App\Data\LotteryTeamData;
use App\Enums\LotteryFormat;
use App\Enums\LotteryMode;
use App\Enums\LotteryPresentationMode;
use App\Enums\LotteryStatus;
use App\Models\Lottery;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/*
|--------------------------------------------------------------------------
| Test Case
|--------------------------------------------------------------------------
|
| The closure you provide to your test functions is always bound to a specific PHPUnit test
| case class. By default, that class is "PHPUnit\Framework\TestCase". Of course, you may
| need to change it using the "pest()" function to bind different classes or traits.
|
*/

pest()->extend(TestCase::class)
    ->use(RefreshDatabase::class)
    ->in('Feature');

/*
|--------------------------------------------------------------------------
| Expectations
|--------------------------------------------------------------------------
|
| When you're writing tests, you often need to check that values meet certain conditions. The
| "expect()" function gives you access to a set of "expectations" methods that you can use
| to assert different things. Of course, you may extend the Expectation API at any time.
|
*/

expect()->extend('toBeOne', function () {
    return $this->toBe(1);
});

/*
|--------------------------------------------------------------------------
| Functions
|--------------------------------------------------------------------------
|
| While Pest is very powerful out-of-the-box, you may have some testing code specific to your
| project that you don't want to repeat in every file. Here you can also expose helpers as
| global functions to help you to reduce the number of lines of code in your test files.
|
*/

function something()
{
    // ..
}

/** @param list<LotteryTeamData> $teams */
function lotteryData(
    LotteryStatus $status = LotteryStatus::Draft,
    array $teams = [],
    LotteryMode $mode = LotteryMode::Official,
    string $name = 'Keeper League Lottery',
    LotteryPresentationMode $presentationMode = LotteryPresentationMode::Manual,
): LotteryData {
    return new LotteryData($name, $mode, LotteryFormat::WeightedSinglePick, $status, $teams, $presentationMode);
}

function lotteryTeamData(
    string $name,
    int $weight,
    int $displayOrder,
    ?string $abbreviation = null,
    ?int $id = null,
): LotteryTeamData {
    return new LotteryTeamData($name, $abbreviation, $weight, $displayOrder, $id);
}

/** @param list<int> $numbers */
function useRandomNumbers(array $numbers): void
{
    app()->instance(RandomNumberGenerator::class, new class($numbers) implements RandomNumberGenerator
    {
        private int $index = 0;

        /** @param list<int> $numbers */
        public function __construct(private readonly array $numbers) {}

        public function between(int $minimum, int $maximum): int
        {
            $number = $this->numbers[$this->index] ?? throw new RuntimeException('No deterministic random number remains.');
            $this->index++;

            if ($number < $minimum || $number > $maximum) {
                throw new RuntimeException('Deterministic random number is outside the requested range.');
            }

            return $number;
        }
    });
}

/** @param list<int> $weights */
function configuredLottery(
    User $owner,
    LotteryMode $mode = LotteryMode::Official,
    LotteryStatus $status = LotteryStatus::Ready,
    array $weights = [4000, 3500, 2500],
    LotteryPresentationMode $presentationMode = LotteryPresentationMode::Manual,
): Lottery {
    $lottery = Lottery::factory()->for($owner, 'owner')->create([
        'mode' => $mode,
        'status' => $status,
        'presentation_mode' => $presentationMode,
    ]);
    $names = ['Wolves', 'Bears', 'Raptors', 'Sharks'];

    foreach ($weights as $index => $weight) {
        $lottery->teams()->create([
            'name' => $names[$index],
            'abbreviation' => strtoupper(substr($names[$index], 0, 3)),
            'weight' => $weight,
            'display_order' => $index + 1,
        ]);
    }

    return $lottery;
}
