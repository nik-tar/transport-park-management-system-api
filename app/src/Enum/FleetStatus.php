<?php

declare(strict_types=1);

namespace App\Enum;

enum FleetStatus: string
{
    case WORKS = 'Works';
    case FREE = 'Free';
    case DOWNTIME = 'Downtime';
}
