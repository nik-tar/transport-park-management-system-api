<?php

namespace App\Repository;

use App\DTO\FleetsListItem;
use App\DTO\Query\GetFleetsListQueryResult;
use App\DTO\Query\ResultInterface;
use App\Entity\Truck;
use App\Enum\FleetStatus;
use App\Query\GetFleetsListQuery;
use App\Query\QueryInterface;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Truck>
 */
class TruckRepository extends ServiceEntityRepository implements EntityRepositoryInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Truck::class);
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
     * @param array<Truck> $trucks
     */
    private function generateResult(array $trucks, GetFleetsListQuery $query, int $total = 0): GetFleetsListQueryResult
    {
        $items = [];

        foreach ($trucks as $truck) {
            if ($query->status === null) {
                $truckInService = $truck->isInService();

                $status = $truckInService ? FleetStatus::Downtime : FleetStatus::Free;
            } else {
                $status = $query->status;
            }

            $subjectType = $truck->getSubjectType();

            $details = $truck->getDetails();

            $items[] = new FleetsListItem(
                id: $truck->getId()->toString(),
                type: $subjectType,
                status: $status,
                details: $details,
            );
        }

        return new GetFleetsListQueryResult(
            items: $items,
            total: count($trucks),
            page: $query->page,
            perPage: $query->perPage,
            pages: (int) ceil($total / $query->perPage),
        );
    }
}
