<?php

declare(strict_types=1);

namespace App\Query\Handler;

use App\DTO\Query\GetFleetsListQueryResult;
use App\DTO\Query\ResultInterface;
use App\Enum\ServiceOrderSubject;
use App\Exception\QueryHandlerException;
use App\Query\GetFleetsListQuery;
use App\Query\QueryInterface;
use App\Repository\FleetSetRepository;
use App\Repository\TrailerRepository;
use App\Repository\TruckRepository;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;

/**
 * @implements QueryHandlerInterface<GetFleetsListQuery, GetFleetsListQueryResult>
 */
#[AutoconfigureTag(
    name: 'app.query_handler',
    attributes: ['query' => GetFleetsListQuery::class]
)]
class GetFleetsListQueryHandler implements QueryHandlerInterface
{
    public function __construct(
        private readonly FleetSetRepository $fleetSetRepository,
        private readonly TrailerRepository $trailerRepository,
        private readonly TruckRepository $truckRepository,
    ) {
    }

    public function __invoke(QueryInterface $query): ResultInterface
    {
        if (!($query instanceof GetFleetsListQuery)) {
            throw new QueryHandlerException('Invalid query type');
        }

        $type = $query->subjectType;

        return match ($type) {
            ServiceOrderSubject::FleetSet => $this->fleetSetRepository->getFleetsList($query),
            ServiceOrderSubject::Trailer => $this->trailerRepository->getFleetsList($query),
            ServiceOrderSubject::Truck => $this->truckRepository->getFleetsList($query),
            default => throw new QueryHandlerException('Invalid subject type'),
        };
    }
}
