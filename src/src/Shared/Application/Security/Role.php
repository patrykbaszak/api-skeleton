<?php

declare(strict_types=1);

namespace App\Shared\Application\Security;

enum Role: string
{
    /**
     * System roles
     */
    case USER = 'ROLE_USER';
    case ADMIN = 'ROLE_ADMIN';
    case SUPER_ADMIN = 'ROLE_SUPER_ADMIN';

    /**
     * Application roles
     */
    // case APP_FORUM_MODERATOR = 'ROLE_APP_FORUM_MODERATOR';

    /**
     * Admin roles
     */
    // case ADMIN_USER_MANAGER = 'ROLE_ADMIN_USER_MANAGER';
    // case ADMIN_FORUM_MANAGER = 'ROLE_ADMIN_FORUM_MANAGER';
}
