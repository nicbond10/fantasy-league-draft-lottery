<?php

declare(strict_types=1);

namespace App\Enums;

enum LotteryMode: string
{
    case Official = 'official';
    case Simulation = 'simulation';
}
