<?php

declare(strict_types=1);

namespace App\Enum;

enum ServiceOrderStatus: string
{
    case OPEN = 'OPEN';
    case IN_PROGRESS = 'IN_PROGRESS';
    case DONE = 'DONE';
    case CANCELED = 'CANCELED';
}
