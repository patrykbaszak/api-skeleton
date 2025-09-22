<?php

declare(strict_types=1);

namespace App\Shared\Domain\Events;

use App\Shared\Domain\Identity\Uuid;
use App\Shared\Domain\Security\Actor;
use DateTimeImmutable;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use JsonSerializable;
use LogicException;
use Stringable;
use Throwable;

/**
 * @SuppressWarnings("PHPMD.NumberOfChildren")
 */
#[ORM\Entity]
#[ORM\Table(name: 'events')]
abstract class Event implements JsonSerializable, Stringable
{
    protected function __construct(
        #[ORM\Id]
        #[ORM\Column(type: 'uuid')]
        public readonly Uuid $id,
        
        #[ORM\Column(type: Types::STRING)]
        public readonly string $actorClass,
        
        #[ORM\Column(type: 'uuid')]
        public readonly Uuid $actorId,
        
        #[ORM\Column(type: Types::STRING)]
        public readonly string $aggregateClass,
        
        #[ORM\Column(type: 'uuid')]
        public readonly Uuid $aggregateId,
        
        #[ORM\Column(type: Types::STRING)]
        public readonly string $eventClass,
        
        /** @var array<string, mixed>|null */
        #[ORM\Column(type: Types::JSON, nullable: true)]
        public ?array $data,
        
        #[ORM\Column(type: Types::TEXT)]
        public string $description,
        
        #[ORM\Column(type: Types::STRING, nullable: true)]
        public readonly ?string $ip,
        
        #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
        public readonly DateTimeImmutable $createdAt,
        
        #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true)]
        public ?DateTimeImmutable $anonymizedAt,
    ) {
    }

    /**
     * @param array<string, mixed>|null $data
     */
    public static function create(
        Actor $actor,
        AggregateRoot $aggregateRoot,
        ?array $data = null,
        ?string $ip = null,
    ): static {
        [$description, $data] = static::apply($actor, $aggregateRoot, $data);
        /** @phpstan-ignore-next-line */
        $event = new static(
            Uuid::v7(),
            $actor::class,
            $actor->id,
            $aggregateRoot::class,
            $aggregateRoot->id,
            static::class,
            $data,
            $description,
            $ip,
            new DateTimeImmutable(),
            null,
        );
        $aggregateRoot->raise($event);

        return $event;
    }

    /**
     * @param class-string              $actorClass
     * @param class-string              $aggregateClass
     * @param class-string              $eventClass
     * @param array<string, mixed>|null $data
     */
    public static function recreate(
        Uuid $id,
        string $actorClass,
        Uuid $actorId,
        string $aggregateClass,
        Uuid $aggregateId,
        string $eventClass,
        ?array $data,
        string $description,
        ?string $ip,
        DateTimeImmutable $createdAt,
        ?DateTimeImmutable $anonymizedAt,
    ): static {
        /** @phpstan-ignore-next-line */
        return new static(
            $id,
            $actorClass,
            $actorId,
            $aggregateClass,
            $aggregateId,
            $eventClass,
            $data,
            $description,
            $ip,
            $createdAt,
            $anonymizedAt,
        );
    }

    /**
     * @param ?array<string, mixed> $data
     * @param AggregateRoot         $entity
     *
     * @return array{0: string, 1: array<string, mixed>}
     */
    abstract protected static function apply(Actor $actor, $entity, ?array $data = null): array;

    /**
     * @param object               $entity
     * @param array<string, mixed> $data
     * @param array<string>        $properties
     *
     * @return array<string, array{was: mixed, is: mixed}>|null
     */
    protected static function handleChanges($entity, array $data, array $properties): ?array
    {
        $changes = [];
        foreach ($properties as $property) {
            if (isset($data[$property]) && $entity->$property !== $data[$property]) {
                try {
                    $entity->$property = $data[$property];
                    $changes[$property] = [
                        'was' => var_export($entity->$property, true),
                        'is' => var_export($data[$property], true),
                    ];
                } catch (Throwable $e) {
                    throw new LogicException("Property `{$property}` is not writable in entity `".$entity::class.'`.', 0, $e);
                }
            }
        }

        if ([] !== $changes && property_exists($entity, 'updatedAt')) {
            $entity->updatedAt = new DateTimeImmutable();
        }

        return $changes;
    }

    /**
     * Method will be used to anonymize actor data when the actor request anonymization.
     */
    public function anonymize(): void
    {
        $this->anonymizedAt = new DateTimeImmutable();
    }

    /**
     * @return array{
     *      id: string,
     *      actorClass: string,
     *      actorId: string,
     *      aggregateClass: string,
     *      aggregateId: string,
     *      eventClass: string,
     *      data: array<string, mixed>|null,
     *      description: string,
     *      ip: string|null,
     *      createdAt: string,
     *      anonymizedAt: string|null,
     * }
     */
    public function jsonSerialize(): array
    {
        return [
            'id' => $this->id->toRfc4122(),
            'actorClass' => $this->actorClass,
            'actorId' => $this->actorId->toRfc4122(),
            'aggregateClass' => $this->aggregateClass,
            'aggregateId' => $this->aggregateId->toRfc4122(),
            'eventClass' => $this->eventClass,
            'data' => $this->data,
            'description' => $this->description,
            'ip' => $this->ip,
            'createdAt' => $this->createdAt->format(DateTimeImmutable::RFC3339),
            'anonymizedAt' => $this->anonymizedAt?->format(DateTimeImmutable::RFC3339),
        ];
    }

    public function __toString(): string
    {
        return $this->id->toString();
    }
}
