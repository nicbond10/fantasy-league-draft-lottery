<?php

declare(strict_types=1);

namespace App\Services;

use App\Contracts\RandomNumberGenerator;

final class SecureRandomNumberGenerator implements RandomNumberGenerator
{
    public function between(int $minimum, int $maximum): int
    {
        return random_int($minimum, $maximum);
    }
}
