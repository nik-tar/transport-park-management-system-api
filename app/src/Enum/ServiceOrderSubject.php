<?php

declare(strict_types=1);

namespace App\Enum;

enum ServiceOrderSubject: string
{
    case TRUCK = 'truck';
    case TRAILER = 'trailer';
    case FLEET_SET = 'fleet_set';
}
