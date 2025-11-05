<?php

declare(strict_types=1);

namespace App\DTO;

use App\DTO\Entity\DetailsInterface;
use App\Enum\FleetStatus;
use App\Enum\ServiceOrderSubject;

final readonly class FleetsListItem implements \JsonSerializable
{
    public function __construct(
        public string $id,
        public ServiceOrderSubject $type,
        public FleetStatus $status,
        public DetailsInterface $details,
    ) {
    }

    public function jsonSerialize(): array
    {
        return [
            'id' => $this->id,
            'type' => $this->type,
            'status' => $this->status,
            'details' => $this->details,
        ];
    }
}
