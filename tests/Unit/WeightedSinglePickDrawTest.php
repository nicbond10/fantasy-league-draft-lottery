<?php

use App\Contracts\RandomNumberGenerator;
use App\Data\LotteryData;
use App\Enums\LotteryStatus;
use App\Services\WeightedSinglePickDraw;

function weightedDrawForTicket(int $ticket): WeightedSinglePickDraw
{
    return new WeightedSinglePickDraw(new class($ticket) implements RandomNumberGenerator
    {
        public function __construct(private readonly int $ticket) {}

        public function between(int $minimum, int $maximum): int
        {
            expect($minimum)->toBe(1)->and($maximum)->toBe(10_000);

            return $this->ticket;
        }
    });
}

function weightedConfiguration(array $teams = []): LotteryData
{
    return lotteryData(
        LotteryStatus::Ready,
        $teams ?: [
            lotteryTeamData('Wolves', 2000, 1, 'WOL', 1),
            lotteryTeamData('Bears', 3000, 2, 'BEA', 2),
            lotteryTeamData('Raptors', 5000, 3, 'RAP', 3),
        ],
    );
}

test('weighted interval boundaries select the expected team', function (int $ticket, string $winner) {
    $result = weightedDrawForTicket($ticket)->draw(10, weightedConfiguration());

    expect($result->winner()->teamName)->toBe($winner);
})->with([
    'first possible ticket' => [1, 'Wolves'],
    'first exact cumulative boundary' => [2000, 'Wolves'],
    'immediately after first boundary' => [2001, 'Bears'],
    'second exact cumulative boundary' => [5000, 'Bears'],
    'immediately after second boundary' => [5001, 'Raptors'],
    'final possible ticket' => [10_000, 'Raptors'],
]);

test('winner moves first while non winners preserve original relative order', function () {
    $result = weightedDrawForTicket(7000)->draw(10, weightedConfiguration());

    expect(array_map(fn ($position) => $position->teamName, $result->positions))
        ->toBe(['Raptors', 'Wolves', 'Bears'])
        ->and(array_map(fn ($position) => $position->finalPosition, $result->positions))->toBe([1, 2, 3])
        ->and(array_sum(array_map(fn ($position) => (int) $position->winner, $result->positions)))->toBe(1)
        ->and(array_unique(array_map(fn ($position) => $position->teamId, $result->positions)))->toHaveCount(3);
});

test('source teams are ordered deterministically by display order', function () {
    $configuration = weightedConfiguration([
        lotteryTeamData('Bears', 3000, 2, id: 2),
        lotteryTeamData('Raptors', 5000, 3, id: 3),
        lotteryTeamData('Wolves', 2000, 1, id: 1),
    ]);

    $result = weightedDrawForTicket(1)->draw(10, $configuration);

    expect(array_map(fn ($position) => $position->teamName, $result->positions))
        ->toBe(['Wolves', 'Bears', 'Raptors']);
});

test('invalid weighted configurations are rejected', function (array $teams) {
    expect(fn () => weightedDrawForTicket(1)->draw(10, weightedConfiguration($teams)))
        ->toThrow(InvalidArgumentException::class);
})->with([
    'not enough teams' => [[lotteryTeamData('Wolves', 10_000, 1)]],
    'zero weight' => [[lotteryTeamData('Wolves', 0, 1), lotteryTeamData('Bears', 10_000, 2)]],
    'invalid total' => [[lotteryTeamData('Wolves', 4000, 1), lotteryTeamData('Bears', 5000, 2)]],
    'noncontiguous order' => [[lotteryTeamData('Wolves', 5000, 1), lotteryTeamData('Bears', 5000, 3)]],
]);
