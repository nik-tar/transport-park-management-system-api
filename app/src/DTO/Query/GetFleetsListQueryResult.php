<?php

declare(strict_types=1);

namespace App\DTO\Query;

use App\DTO\FleetsListItem;

final readonly class GetFleetsListQueryResult implements ResultInterface
{
    /**
     * @param array<FleetsListItem> $items
     */
    public function __construct(
        public array $items,
        public int $total,
        public int $page,
        public int $perPage,
        public int $pages,
    ) {
    }

    /**
     * @param array<FleetsListItem> $items
     */
    public static function create(array $items, int $total, int $page, int $perPage): self
    {
        $totalPages = (int) ceil($total / $perPage);

        return new self($items, $total, $page, $perPage, $totalPages);
    }

    public function jsonSerialize(): array
    {
        return [
            'items' => $this->items,
            'total' => $this->total,
            'page' => $this->page,
            'per_page' => $this->perPage,
            'pages' => $this->pages,
        ];
    }
}
