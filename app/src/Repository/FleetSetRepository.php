<?php

namespace App\Repository;

use App\DTO\FleetsListItem;
use App\DTO\Query\GetFleetsListQueryResult;
use App\DTO\Query\ResultInterface;
use App\Entity\FleetSet;
use App\Enum\FleetStatus;
use App\Query\GetFleetsListQuery;
use App\Query\QueryInterface;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<FleetSet>
 */
class FleetSetRepository extends ServiceEntityRepository implements EntityRepositoryInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, FleetSet::class);
    }

    /**
     * @param GetFleetsListQuery $query
     * @return GetFleetsListQueryResult
     */
    public function getFleetsList(QueryInterface $query): ResultInterface
    {
        $qb = $this->createQueryBuilder('fs')
            ->leftJoin('fs.truck', 't')
            ->leftJoin('fs.trailer', 'tr')
            ->leftJoin('fs.drivers', 'dr')
            ->addSelect('t', 'tr', 'dr');

        $statusFilter = $query->status;

        if ($statusFilter !== null) {
            if ($statusFilter === FleetStatus::Downtime) {
                $qb->andWhere('t.inService = :inService OR tr.inService = :inService')
                    ->setParameter('inService', true);
            } elseif ($statusFilter === FleetStatus::Free) {
                $qb->andWhere('t.inService = :inService AND tr.inService = :inService')
                    ->setParameter('inService', false)
                    ->andWhere("(SELECT COUNT(d.id) FROM App\Entity\Driver d
          JOIN d.fleetSets fsd WHERE fsd.id = fs.id) = :driversCount")
                    ->setParameter('driversCount', 0);
            } elseif ($statusFilter === FleetStatus::Works) {
                $qb->andWhere('t.inService = :inService AND tr.inService = :inService')
                    ->setParameter('inService', false)
                    ->andWhere("(SELECT COUNT(d.id) FROM App\Entity\Driver d
          JOIN d.fleetSets fsd WHERE fsd.id = fs.id) > :driversCount")
                    ->setParameter('driversCount', 0);
            }
        }

        $qb->orderBy('fs.updatedAt', 'DESC');

        $countQb = clone $qb;
        $total = (int) $countQb
            ->select('COUNT(DISTINCT fs.id)')
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
     * @param array<FleetSet> $fleetSets
     */
    private function generateResult(array $fleetSets, GetFleetsListQuery $query, int $total = 0): GetFleetsListQueryResult
    {
        $items = [];

        foreach ($fleetSets as $fleetSet) {
            if ($query->status === null) {
                $truckInService = $fleetSet->getTruck()->isInService();
                $trailerInService = $fleetSet->getTrailer()->isInService();
                $driversCount = $fleetSet->getDrivers()->count();

                $status = match (true) {
                    $truckInService || $trailerInService => FleetStatus::Downtime,
                    !$truckInService && !$trailerInService && $driversCount > 0 => FleetStatus::Works,
                    !$truckInService && !$trailerInService && $driversCount === 0 => FleetStatus::Free,
                };
            } else {
                $status = $query->status;
            }

            $subjectType = $fleetSet->getSubjectType();

            $details = $fleetSet->getDetails();

            $items[] = new FleetsListItem(
                id: $fleetSet->getId()->toString(),
                type: $subjectType,
                status: $status,
                details: $details,
            );
        }

        return new GetFleetsListQueryResult(
            items: $items,
            total: count($fleetSets),
            page: $query->page,
            perPage: $query->perPage,
            pages: (int) ceil($total / $query->perPage),
        );
    }
}
