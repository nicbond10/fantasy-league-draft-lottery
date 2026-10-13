<?php

declare(strict_types=1);

namespace App\Livewire\Lotteries;

use App\Data\LotteryData;
use App\Data\LotteryTeamData;
use App\Enums\LotteryFormat;
use App\Enums\LotteryMode;
use App\Enums\LotteryPresentationMode;
use App\Enums\LotteryStatus;
use App\Support\Percentage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Livewire\Component;

abstract class LotteryForm extends Component
{
    public string $name = '';

    public string $mode = 'official';

    public string $presentationMode = 'manual';

    /** @var array<string, array{key: string, name: string, abbreviation: string, percentage: string}> */
    public array $teams = [];

    public function addTeam(): void
    {
        if (count($this->teams) >= 64) {
            $this->addError('teams', 'A lottery may not contain more than 64 teams.');

            return;
        }

        $key = (string) Str::uuid();
        $this->teams[$key] = [
            'key' => $key,
            'name' => '',
            'abbreviation' => '',
            'percentage' => '0.00',
        ];
    }

    public function removeTeam(string $key): void
    {
        if (! array_key_exists($key, $this->teams)) {
            return;
        }

        unset($this->teams[$key]);
    }

    protected function dataFor(LotteryStatus $status): LotteryData
    {
        $validated = $this->validate([
            'name' => ['required', 'string', 'max:120'],
            'mode' => ['required', Rule::enum(LotteryMode::class)],
            'presentationMode' => ['required', Rule::enum(LotteryPresentationMode::class)],
            'teams' => ['array', 'max:64'],
            'teams.*.key' => ['required', 'string', 'distinct'],
            'teams.*.name' => [Rule::requiredIf($status === LotteryStatus::Ready), 'nullable', 'string', 'max:120'],
            'teams.*.abbreviation' => ['nullable', 'string', 'max:10'],
            'teams.*.percentage' => ['required', 'string', 'regex:/^(?:0|[1-9]\d{0,2})(?:\.\d{1,2})?$/'],
        ], [
            'teams.*.percentage.regex' => 'Lottery odds must be between 0 and 100 with no more than two decimal places.',
        ]);

        /** @var array<string, array{key: string, name: string|null, abbreviation: string|null, percentage: string}> $validatedTeams */
        $validatedTeams = $validated['teams'];

        $teams = [];

        foreach ($validatedTeams as $key => $team) {
            $name = trim((string) ($team['name'] ?? ''));
            $abbreviation = trim((string) ($team['abbreviation'] ?? ''));
            $weight = Percentage::toBasisPoints($team['percentage']);

            if ($status === LotteryStatus::Draft && $name === '' && $abbreviation === '' && $weight === 0) {
                continue;
            }

            $teams[] = new LotteryTeamData(
                name: $name,
                abbreviation: $abbreviation === '' ? null : Str::upper($abbreviation),
                weight: $weight,
                displayOrder: count($teams) + 1,
                formKey: $key,
            );
        }

        return new LotteryData(
            name: trim($validated['name']),
            mode: LotteryMode::from($validated['mode']),
            format: LotteryFormat::WeightedSinglePick,
            status: $status,
            teams: $teams,
            presentationMode: LotteryPresentationMode::from($validated['presentationMode']),
        );
    }
}
