<?php

declare(strict_types=1);

namespace App\Shared\Application\DataTransferObject;

use ArrayAccess;
use ArrayIterator;
use Countable;
use IteratorAggregate;
use JsonSerializable;
use RuntimeException;

/**
 * @template T
 *
 * @implements ArrayAccess<int, T>
 * @implements IteratorAggregate<int, T>
 */
class Collection implements JsonSerializable, ArrayAccess, Countable, IteratorAggregate
{
    /**
     * @param array<T> $items
     */
    public function __construct(
        public array $items,
    ) {
    }

    /**
     * @return array{
     *      count: int,
     *      items: array<T>
     * }
     */
    public function jsonSerialize(): array
    {
        return [
            'count' => $this->count(),
            'items' => $this->items,
        ];
    }

    public function count(): int
    {
        return count($this->items);
    }

    /**
     * @param Collection<T> $collection
     *
     * @return Collection<T>
     */
    public function merge(Collection $collection): self
    {
        return new self(
            array_merge($this->items, $collection->items),
        );
    }

    /**
     * @param callable(T): bool $callback
     *
     * @return T|null
     *
     * @phpstan-return (T&object)|null
     */
    public function searchOne(callable $callback): ?object
    {
        foreach ($this->items as $item) {
            if ($callback($item)) {
                return $item;
            }
        }

        return null;
    }

    /**
     * @param callable(T): bool $callback
     *
     * @return Collection<T>
     */
    public function search(callable $callback): Collection
    {
        return new self(
            array_filter($this->items, $callback),
        );
    }

    /**
     * @param callable(T): T $callback
     *
     * @return Collection<T>
     */
    public function map(callable $callback): self
    {
        return new self(
            array_map($callback, $this->items),
        );
    }

    /**
     * @param callable(T, T): int $callback
     *
     * @return Collection<T>
     */
    public function sort(callable $callback): self
    {
        $items = $this->items;
        usort($items, $callback);

        return new self(
            $items,
        );
    }

    public function isEmpty(): bool
    {
        return [] === $this->items;
    }

    /**
     * @return T|null
     *
     * @phpstan-return T|null
     */
    public function first()
    {
        return $this->items[0] ?? null;
    }

    /**
     * @return T|null
     *
     * @phpstan-return T|null
     */
    public function last()
    {
        return $this->items[count($this->items) - 1] ?? null;
    }

    /**
     * @param T $item
     */
    public function add($item): void
    {
        $this->items[] = $item;
    }

    /**
     * @return ArrayIterator<int, T>
     */
    public function getIterator(): ArrayIterator
    {
        return new ArrayIterator($this->items);
    }

    /**
     * @param int $offset
     */
    public function offsetExists(mixed $offset): bool
    {
        return isset($this->items[$offset]);
    }

    /**
     * @param int $offset
     *
     * @return T|null
     */
    public function offsetGet(mixed $offset): mixed
    {
        return $this->items[$offset] ?? null;
    }

    /**
     * @param int $offset
     * @param T   $value
     *
     * @SuppressWarnings("PHPMD.UnusedFormalParameter")
     */
    public function offsetSet(mixed $offset, mixed $value): void
    {
        throw new RuntimeException('Collection is immutable');
    }

    /**
     * @param int $offset
     *
     * @SuppressWarnings("PHPMD.UnusedFormalParameter")
     */
    public function offsetUnset(mixed $offset): void
    {
        throw new RuntimeException('Collection is immutable');
    }
}
