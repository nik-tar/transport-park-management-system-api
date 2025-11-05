<?php

declare(strict_types=1);

namespace App\Repository;

use App\DTO\Query\ResultInterface;
use App\Query\QueryInterface;

interface EntityRepositoryInterface
{
    public function getFleetsList(QueryInterface $query): ResultInterface;
}
