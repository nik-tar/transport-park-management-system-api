<?php

declare(strict_types=1);

namespace App\Controller;

use App\Enum\FleetStatus;
use App\Enum\ServiceOrderSubject;
use App\Query\GetFleetsListQuery;
use App\Query\Handler\GetFleetsListQueryHandler;
use App\Registry\QueryHandlerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api')]
final class FleetsController extends AbstractController
{
    public function __construct(
        private readonly QueryHandlerRegistry $queryHandlerRegistry,
    ) {
    }

    #[Route('/fleets-list', methods: ['GET'])]
    public function getList(Request $request): JsonResponse
    {
        $status = $request->query->get('status');
        $type = $request->query->get('type', ServiceOrderSubject::FleetSet->value);
        $page = (int) ($request->query->get('page', 1));
        $perPage = (int) ($request->query->get('per_page', 20));

        $query = new GetFleetsListQuery(
            FleetStatus::tryFrom($status),
            ServiceOrderSubject::tryFrom($type) ?? ServiceOrderSubject::FleetSet,
            $page,
            $perPage,
        );

        /** @var GetFleetsListQueryHandler $handler */
        $handler = $this->queryHandlerRegistry->getByQueryObject($query);

        $result = $handler($query);

        return new JsonResponse($result);
    }
}
