<?php

declare(strict_types=1);

namespace App\Data;

final readonly class LotteryTeamData
{
    public function __construct(
        public string $name,
        public ?string $abbreviation,
        public int $weight,
        public int $displayOrder,
        public ?int $id = null,
        public ?string $formKey = null,
    ) {}
}
