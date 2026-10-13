<?php

declare(strict_types=1);

namespace App\Services;

use App\Data\LotteryData;
use App\Enums\LotteryStatus;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

final class LotteryConfigurationValidator
{
    public function validate(LotteryData $data): void
    {
        $errors = [];

        if (trim($data->name) === '' || Str::length(trim($data->name)) > 120) {
            $errors['name'] = 'The lottery name is required and may not exceed 120 characters.';
        }

        $normalizedNames = [];
        $orders = [];

        foreach ($data->teams as $index => $team) {
            $name = trim($team->name);
            $errorKey = $team->formKey ?? (string) $index;

            if ($name === '' || Str::length($name) > 120) {
                $errors["teams.{$errorKey}.name"] = 'Each configured team must have a name of 120 characters or fewer.';
            }

            $normalizedName = Str::lower($name);

            if ($name !== '' && in_array($normalizedName, $normalizedNames, true)) {
                $errors["teams.{$errorKey}.name"] = 'Team names must be unique within a lottery.';
            }

            $normalizedNames[] = $normalizedName;

            if ($team->abbreviation !== null && Str::length(trim($team->abbreviation)) > 10) {
                $errors["teams.{$errorKey}.abbreviation"] = 'Team abbreviations may not exceed 10 characters.';
            }

            if ($team->weight < 0 || $team->weight > 10_000) {
                $errors["teams.{$errorKey}.percentage"] = 'Lottery odds must be between 0.00% and 100.00%.';
            }

            $orders[] = $team->displayOrder;
        }

        if ($orders !== [] && $orders !== range(1, count($orders))) {
            $errors['teams'] = 'Team order must be complete and contiguous.';
        }

        if ($data->status === LotteryStatus::Ready) {
            if (count($data->teams) < 2) {
                $errors['teams'] = 'A ready lottery requires at least two teams.';
            }

            foreach ($data->teams as $index => $team) {
                $errorKey = $team->formKey ?? (string) $index;

                if ($team->weight <= 0) {
                    $errors["teams.{$errorKey}.percentage"] = 'Every team in a ready lottery must have positive odds.';
                }
            }

            if (array_sum(array_map(static fn ($team): int => $team->weight, $data->teams)) !== 10_000) {
                $errors['teams_total'] = 'Lottery odds must total exactly 100.00% before marking the lottery ready.';
            }
        }

        if ($errors !== []) {
            throw ValidationException::withMessages($errors);
        }
    }
}
