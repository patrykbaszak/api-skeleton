<?php

declare(strict_types=1);

namespace App\Shared\Application\Security\Actor;

use App\Shared\Application\Security\AccessDeniedException;
use App\Shared\Application\Security\Role;
use App\Shared\Domain\Identity\Uuid;
use Symfony\Component\HttpFoundation\Request;

class SuperAdmin extends Admin
{
    /**
     * @param Role[] $roles
     */
    public function __construct(
        Uuid $id,
        array $roles,
        ?Request $request = null,
    ) {
        AccessDeniedException::checkActorHasRole($this, [Role::SUPER_ADMIN, Role::ADMIN], 'initializing super admin actor');

        parent::__construct(
            $id,
            $roles,
            $request,
        );
    }
}
