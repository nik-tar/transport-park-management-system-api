<?php

declare(strict_types=1);

namespace App\Enum;

enum FleetStatus: string
{
    case Works = 'Works';
    case Free = 'Free';
    case Downtime = 'Downtime';
}
