<?php

declare(strict_types=1);

namespace App\DTO\Entity;

final readonly class FleetSetDetails implements DetailsInterface
{
    public function __construct(
        public string $truckId,
        public string $trailerId,
        public int $driversCount,
        public bool $isInService = false,
    ) {
    }

    public function jsonSerialize(): array
    {
        return [
            'truck_id' => $this->truckId,
            'trailer_id' => $this->trailerId,
            'drivers_count' => $this->driversCount,
            'is_in_service' => $this->isInService,
        ];
    }
}
