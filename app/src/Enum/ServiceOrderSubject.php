<?php

declare(strict_types=1);

namespace App\Enum;

enum ServiceOrderSubject: string
{
    case Truck = 'Truck';
    case Trailer = 'Trailer';
    case FleetSet = 'FleetSet';
}
