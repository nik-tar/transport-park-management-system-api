<?php

declare(strict_types=1);

namespace App\Query;

use App\Enum\FleetStatus;
use App\Enum\ServiceOrderSubject;

final readonly class GetFleetsListQuery implements QueryInterface
{
    public function __construct(
        public ?FleetStatus $status = null,
        public ServiceOrderSubject $subjectType = ServiceOrderSubject::FleetSet,
        public int $page = 1,
        public int $perPage = 20,
    ) {
    }

    public function getOffset(): int
    {
        return ($this->page - 1) * $this->perPage;
    }
}
