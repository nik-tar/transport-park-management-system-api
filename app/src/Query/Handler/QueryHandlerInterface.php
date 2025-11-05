<?php

declare(strict_types=1);

namespace App\Query\Handler;

use App\DTO\Query\ResultInterface;
use App\Query\QueryInterface;

/**
 * @template TQuery of QueryInterface
 * @template TQueryResult of ResultInterface
 */
interface QueryHandlerInterface
{
    /**
     * @param TQuery $query
     * @return TQueryResult
     */
    public function __invoke(QueryInterface $query): ResultInterface;
}
