<?php

declare(strict_types=1);

namespace App\DTO\Entity;

final readonly class TruckDetails implements DetailsInterface
{
    public function __construct(
        public string $model,
        public string $brand,
        public string $plateNumber,
        public bool $isInService = false,
    ) {
    }

    public function jsonSerialize(): array
    {
        return [
            'model' => $this->model,
            'brand' => $this->brand,
            'plate_number' => $this->plateNumber,
            'is_in_service' => $this->isInService,
        ];
    }
}
