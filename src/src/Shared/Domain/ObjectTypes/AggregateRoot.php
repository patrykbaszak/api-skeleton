<?php

declare(strict_types=1);

namespace App\Shared\Domain\ObjectTypes;

use App\Shared\Domain\Identity\Uuid;
use DateTimeImmutable;
use JsonSerializable;
use Stringable;

/**
 * @method static create()
 * @method static recreate()
 *
 * @SuppressWarnings("PHPMD.NumberOfChildren")
 */
abstract class AggregateRoot implements JsonSerializable, Stringable
{
    /**
     * @var Event[]
     */
    protected array $events = [];

    protected function __construct(
        public readonly Uuid $id,
        public readonly DateTimeImmutable $createdAt,
        public ?DateTimeImmutable $updatedAt = null,
    ) {
    }

    /**
     * @return Event[]
     */
    final public function pullEvents(): array
    {
        $events = $this->events;
        $this->events = [];

        return $events;
    }

    final public function raise(Event $event): void
    {
        $this->events[] = $event;
    }

    public function __toString(): string
    {
        return $this->id->toString();
    }
}
