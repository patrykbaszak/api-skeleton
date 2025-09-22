<?php

declare(strict_types=1);

namespace App\Shared\Application\Security\Actor;

use App\Shared\Application\Security\Role;
use App\Shared\Domain\Identity\Uuid;

/**
 * Each command / query action requires actor
 * System actor is used when no actor is available
 * or when actions requires the highest level of permissions.
 */
final class System extends SuperAdmin
{
    public const ID = '11111111-1111-1111-1111-111111111111';

    public function __construct()
    {
        parent::__construct(
            new Uuid(self::ID),
            [
                Role::USER,
                Role::ADMIN,
                Role::SUPER_ADMIN,
            ],
        );
    }
}
