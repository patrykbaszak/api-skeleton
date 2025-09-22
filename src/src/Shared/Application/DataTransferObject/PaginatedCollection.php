<?php

declare(strict_types=1);

namespace App\Shared\Application\DataTransferObject;

/**
 * @template T
 *
 * @extends Collection<T>
 */
final class PaginatedCollection extends Collection
{
    /**
     * @param array<T> $items
     */
    public function __construct(
        public readonly int $total,
        public readonly int $offset,
        public readonly int $limit,
        array $items,
    ) {
        parent::__construct($items);
    }

    /**
     * @param callable(T): T $callback
     *
     * @return PaginatedCollection<T>
     */
    public function map(callable $callback): self
    {
        return new self(
            $this->total,
            $this->offset,
            $this->limit,
            array_map($callback, $this->items),
        );
    }

    public function hasNext(): bool
    {
        return $this->offset + $this->limit < $this->total;
    }

    public function nextOffset(): int
    {
        return $this->offset + $this->limit;
    }

    public function hasPrevious(): bool
    {
        return $this->offset > 0;
    }

    public function previousOffset(): int
    {
        return max(0, $this->offset - $this->limit);
    }

    /**
     * @return array{total: int, offset: int, limit: int, items: array<T>}
     *
     * @phpstan-return array<string, mixed>
     */
    public function jsonSerialize(): array
    {
        return [
            'total' => $this->total,
            'offset' => $this->offset,
            'limit' => $this->limit,
            'items' => $this->items,
        ];
    }
}
