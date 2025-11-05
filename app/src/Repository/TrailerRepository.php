<?php

namespace App\Repository;

use App\DTO\FleetsListItem;
use App\DTO\Query\GetFleetsListQueryResult;
use App\DTO\Query\ResultInterface;
use App\Entity\Trailer;
use App\Enum\FleetStatus;
use App\Query\GetFleetsListQuery;
use App\Query\QueryInterface;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Trailer>
 */
class TrailerRepository extends ServiceEntityRepository implements EntityRepositoryInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Trailer::class);
    }

    /**
     * @param GetFleetsListQuery $query
     * @return GetFleetsListQueryResult
     */
    public function getFleetsList(QueryInterface $query): ResultInterface
    {
        $qb = $this->createQueryBuilder('t');

        $statusFilter = $query->status;

        if ($statusFilter !== null) {
            if ($statusFilter === FleetStatus::Downtime) {
                $qb->andWhere('t.inService = :inService')
                    ->setParameter('inService', true);
            } elseif ($statusFilter === FleetStatus::Free) {
                $qb->andWhere('t.inService = :inService')
                    ->setParameter('inService', false);
            }
        }

        $qb->orderBy('t.updatedAt', 'DESC');

        $countQb = clone $qb;
        $total = (int) $countQb
            ->select('COUNT(DISTINCT t.id)')
            ->getQuery()
            ->getSingleScalarResult();

        $items = $qb
            ->setFirstResult($query->getOffset())
            ->setMaxResults($query->perPage)
            ->getQuery()
            ->getResult();

        return $this->generateResult($items, $query, $total);
    }

    /**
     * @param array<Trailer> $trailers
     */
    private function generateResult(array $trailers, GetFleetsListQuery $query, int $total = 0): GetFleetsListQueryResult
    {
        $items = [];

        foreach ($trailers as $trailer) {
            if ($query->status === null) {
                $trailerInService = $trailer->isInService();

                $status = $trailerInService ? FleetStatus::Downtime : FleetStatus::Free;
            } else {
                $status = $query->status;
            }

            $subjectType = $trailer->getSubjectType();

            $details = $trailer->getDetails();

            $items[] = new FleetsListItem(
                id: $trailer->getId()->toString(),
                type: $subjectType,
                status: $status,
                details: $details,
            );
        }

        return new GetFleetsListQueryResult(
            items: $items,
            total: count($trailers),
            page: $query->page,
            perPage: $query->perPage,
            pages: (int) ceil($total / $query->perPage),
        );
    }
}
