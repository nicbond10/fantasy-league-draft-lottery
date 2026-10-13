<?php

declare(strict_types=1);

namespace App\Support;

use InvalidArgumentException;

final class Percentage
{
    public static function toBasisPoints(string $percentage): int
    {
        $percentage = trim($percentage);

        if (! preg_match('/^(?:0|[1-9]\d{0,2})(?:\.(\d{1,2}))?$/', $percentage, $matches)) {
            throw new InvalidArgumentException('Percentage must have no more than two decimal places.');
        }

        [$whole] = explode('.', $percentage, 2);
        $fraction = str_pad($matches[1] ?? '', 2, '0');
        $basisPoints = ((int) $whole * 100) + (int) $fraction;

        if ($basisPoints > 10_000) {
            throw new InvalidArgumentException('Percentage cannot be greater than 100.00.');
        }

        return $basisPoints;
    }

    public static function fromBasisPoints(int $basisPoints): string
    {
        if ($basisPoints < 0) {
            throw new InvalidArgumentException('Basis points cannot be negative.');
        }

        return sprintf('%d.%02d', intdiv($basisPoints, 100), $basisPoints % 100);
    }
}
