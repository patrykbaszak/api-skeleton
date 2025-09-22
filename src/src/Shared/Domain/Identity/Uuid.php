<?php

declare(strict_types=1);

namespace App\Shared\Domain\Identity;

use RuntimeException;
use Stringable;
use Symfony\Component\Uid\Uuid as SymfonyUuid;

/**
 * @SuppressWarnings("PHPMD.ShortMethodName")
 */
final readonly class Uuid implements Stringable
{
    public function __construct(private string $value)
    {
    }

    public static function new(): self
    {
        return self::v7();
    }

    public static function v4(): self
    {
        return new self(SymfonyUuid::v4()->toRfc4122());
    }

    public static function v7(): self
    {
        return new self(SymfonyUuid::v7()->toRfc4122());
    }

    public static function fromString(string $value): self
    {
        $uuid = new self($value);
        $uuid->validate();

        return $uuid;
    }

    public function equals(string|self $other): bool
    {
        if (is_string($other)) {
            return $this->value === $other;
        }

        return $this->value === $other->value;
    }

    public function validate(): void
    {
        if (!SymfonyUuid::isValid($this->value)) {
            throw new RuntimeException('Uuid is not valid.');
        }
    }

    public function toRfc4122(): string
    {
        return $this->value;
    }

    public function toString(): string
    {
        return $this->value;
    }

    public function __toString(): string
    {
        return $this->value;
    }
}
