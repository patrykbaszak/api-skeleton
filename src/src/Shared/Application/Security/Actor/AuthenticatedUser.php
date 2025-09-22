<?php

declare(strict_types=1);

namespace App\Shared\Application\Security\Actor;

use App\Shared\Application\Security\Role;
use App\Shared\Domain\Identity\Uuid;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\User\UserInterface;

class AuthenticatedUser extends Guest implements UserInterface
{
    /**
     * @param Role[] $roles
     */
    public function __construct(
        Uuid $id,
        public readonly array $roles,
        ?Request $request = null,
    ) {
        parent::__construct($request, $id);
    }

    /**
     * Returns the roles of the user as strings.
     *
     * @return array<string>
     */
    public function getRoles(): array
    {
        return array_map(static fn (Role $role): string => $role->value, $this->roles);
    }

    /**
     * Returns the role objects of the user.
     *
     * @return Role[]
     */
    public function getAllRoles(): array
    {
        return $this->roles;
    }

    public function hasRole(Role $role): bool
    {
        return in_array($role, $this->roles, true);
    }

    public function eraseCredentials(): void
    {
        // do nothing
    }

    public function getUserIdentifier(): string
    {
        return $this->id->toRfc4122();
    }
}
