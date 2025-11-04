<?php

declare(strict_types=1);

namespace App\Entity\Contract;

use App\Enum\ServiceOrderSubject;

interface ServiceableInterface
{
    public function getSubjectType(): ServiceOrderSubject;
}
