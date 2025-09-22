<?php

declare(strict_types=1);

namespace App\Shared\Presentation\View;

use App\Shared\Application\DataTransferObject\Collection;
use App\Shared\Application\DataTransferObject\PaginatedCollection;
use JsonSerializable;
use Symfony\Component\HttpFoundation\Request;

/**
 * @template T
 */
final class PaginatedCollectionWithLinks implements JsonSerializable
{
    private ?bool $isItemJsonSerializable = null;

    /**
     * @param PaginatedCollection<T> $collection
     */
    private function __construct(
        private readonly PaginatedCollection $collection,
        private readonly string $pattern,
        private readonly ?string $itemPattern = null,
    ) {
    }

    /**
     * @template U
     *
     * @param Collection<U>|PaginatedCollection<U> $collection
     *
     * @return self<U>|Collection<U>
     */
    public static function fromRequest(
        Collection|PaginatedCollection $collection,
        Request $request,
        ?string $itemPattern = null,
        string $limit = 'limit',
        string $offset = 'offset',
    ): self|Collection {
        if (!$collection instanceof PaginatedCollection) {
            return $collection;
        }

        $limitValue = $request->query->get($limit, null);
        $offsetValue = $request->query->get($offset, null);

        $limitValueCast = null !== $limitValue ? (int) $limitValue : null;
        $offsetValueCast = null !== $offsetValue ? (int) $offsetValue : null;

        return new self(
            $collection,
            self::buildPattern($request->getUri(), $limit, $limitValueCast, $offset, $offsetValueCast),
            $itemPattern,
        );
    }

    private static function buildPattern(
        string $requestUri,
        string $limit,
        int|string|null $limitValue,
        string $offset,
        int|string|null $offsetValue,
    ): string {
        $limitSearch = null !== $limitValue ? "{$limit}={$limitValue}" : null;
        $limitReplace = "{$limit}={limit}";

        $offsetSearch = null !== $offsetValue ? "{$offset}={$offsetValue}" : null;
        $offsetReplace = "{$offset}={offset}";

        $pattern = str_replace(
            array_filter([$limitSearch, $offsetSearch]),
            array_filter([$limitSearch ? $limitReplace : null, $offsetSearch ? $offsetReplace : null]),
            $requestUri,
        );

        // Append placeholders for missing pagination parameters using a dedicated helper.
        if (null === $limitValue) {
            $pattern = self::appendQueryParameter($pattern, $limit, '{limit}');
        }

        if (null === $offsetValue) {
            return self::appendQueryParameter($pattern, $offset, '{offset}');
        }

        return $pattern;
    }

    /**
     * Adds a query parameter placeholder to the pattern without relying on an "else" branch.
     */
    private static function appendQueryParameter(string $pattern, string $paramName, string $placeholder): string
    {
        $separator = str_contains($pattern, '?') ? '&' : '?';

        return "{$pattern}{$separator}{$paramName}={$placeholder}";
    }

    private function applyItemPattern(mixed $item): mixed
    {
        if (null === $this->isItemJsonSerializable) {
            $this->isItemJsonSerializable = $item instanceof JsonSerializable && property_exists($item, 'id');
        }

        if ($this->isItemJsonSerializable) {
            return array_merge(['_link' => $this->itemPattern . (string) $item->id], $item->jsonSerialize());
        }

        return $item;
    }

    /**
     * @return array{
     *      _next?: string,
     *      _previous?: string,
     *      total: int,
     *      offset: int,
     *      limit: int,
     *      items: array<mixed>
     * }
     */
    public function jsonSerialize(): array
    {
        $output = [
            'total' => $this->collection->total,
            'offset' => $this->collection->offset,
            'limit' => $this->collection->limit,
        ];

        if ($this->collection->hasNext()) {
            $output['_next'] = str_replace(
                ['{offset}', '{limit}'],
                [(string) $this->collection->nextOffset(), (string) $this->collection->limit],
                $this->pattern,
            );
        }

        if ($this->collection->hasPrevious()) {
            $output['_previous'] = str_replace(
                ['{offset}', '{limit}'],
                [(string) $this->collection->previousOffset(), (string) $this->collection->limit],
                $this->pattern,
            );
        }

        $items = $this->itemPattern
            ? array_map(fn ($item) => $this->applyItemPattern($item), $this->collection->items)
            : $this->collection->items;

        $output['items'] = $items;

        return $output;
    }
}
