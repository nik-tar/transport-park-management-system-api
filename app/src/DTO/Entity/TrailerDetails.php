<?php

declare(strict_types=1);

namespace App\DTO\Entity;

final readonly class TrailerDetails implements DetailsInterface
{
    public function __construct(
        public string $plateNumber,
        public bool $isInService = false,
    ) {
    }

    public function jsonSerialize(): array
    {
        return [
            'plate_number' => $this->plateNumber,
            'is_in_service' => $this->isInService,
        ];
    }
}
