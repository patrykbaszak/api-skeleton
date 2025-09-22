<?php

declare(strict_types=1);

namespace App\Shared\Application\Security\Actor;

use App\Shared\Application\Security\Role;
use App\Shared\Domain\Identity\Uuid;
use App\Shared\Domain\Security\Actor;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\User\UserInterface;

class Guest extends Actor implements UserInterface
{
    public const ID = '00000000-0000-0000-0000-000000000000';

    public function __construct(
        public readonly ?Request $request = null,
        ?Uuid $id = null,
    ) {
        parent::__construct($id ?? new Uuid(self::ID));
    }

    public function getRoles(): array
    {
        return [];
    }

    /**
     * @SuppressWarnings("PMD.UnusedFormalParameter")
     */
    public function hasRole(Role $role): bool
    {
        return false;
    }

    public function eraseCredentials(): void
    {
        // do nothing
    }

    public function getUserIdentifier(): string
    {
        return self::ID;
    }

    public function getIpAddress(): ?string
    {
        return $this->request?->getClientIp();
    }
}
