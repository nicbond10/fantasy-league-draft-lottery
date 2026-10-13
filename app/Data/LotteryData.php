<?php

declare(strict_types=1);

namespace App\Data;

use App\Enums\LotteryFormat;
use App\Enums\LotteryMode;
use App\Enums\LotteryPresentationMode;
use App\Enums\LotteryStatus;

final readonly class LotteryData
{
    /** @param list<LotteryTeamData> $teams */
    public function __construct(
        public string $name,
        public LotteryMode $mode,
        public LotteryFormat $format,
        public LotteryStatus $status,
        public array $teams,
        public LotteryPresentationMode $presentationMode = LotteryPresentationMode::Manual,
    ) {}
}
