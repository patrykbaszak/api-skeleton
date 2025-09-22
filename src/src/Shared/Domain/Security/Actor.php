<?php

declare(strict_types=1);

namespace App\Shared\Domain\Security;

use App\Shared\Domain\Identity\Uuid;
use ReflectionClass;
use Stringable;

abstract class Actor implements Stringable
{
    public function __construct(
        public readonly Uuid $id,
    ) {
    }

    public function __toString(): string
    {
        return $this->id->toRfc4122();
    }

    public static function type(): string
    {
        return implode(' ', preg_split(
            '/(?=[A-Z])/',
            (new ReflectionClass(static::class))->getShortName(),
            -1,
            PREG_SPLIT_NO_EMPTY
        ));
    }
}
