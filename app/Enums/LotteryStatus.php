<?php

declare(strict_types=1);

namespace App\Enums;

enum LotteryStatus: string
{
    case Draft = 'draft';
    case Ready = 'ready';
    case Completed = 'completed';
    case Archived = 'archived';
}
