<?php

declare(strict_types=1);

namespace App\Enum;

enum ServiceOrderStatus: string
{
    case Open = 'Open';
    case InProgress = 'InProgress';
    case Done = 'Done';
    case Cancelled = 'Cancelled';
}
