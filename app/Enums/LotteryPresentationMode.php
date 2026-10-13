<?php

declare(strict_types=1);

namespace App\Enums;

enum LotteryPresentationMode: string
{
    case Manual = 'manual';
    case Autoplay = 'autoplay';
}
