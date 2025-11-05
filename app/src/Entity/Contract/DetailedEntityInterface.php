<?php

declare(strict_types=1);

namespace App\Entity\Contract;

use App\DTO\Entity\DetailsInterface;

interface DetailedEntityInterface
{
    public function getDetails(): DetailsInterface;
}
