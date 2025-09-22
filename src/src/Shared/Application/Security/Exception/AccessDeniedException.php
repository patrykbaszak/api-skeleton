<?php

namespace App\Shared\Application\Security\Exception;

use App\Shared\Application\Security\Actor;
use App\Shared\Application\Security\Role;
use JsonSerializable;
use RuntimeException;
use Throwable;

class AccessDeniedException extends RuntimeException implements JsonSerializable
{
    public const TRANSLATION_KEY_PREFIX = 'application.security.exception.access_denied.';
    public const TRANSLATION_KEY_INSTANCE_OF = self::TRANSLATION_KEY_PREFIX . 'instance_of';
    public const TRANSLATION_KEY_HAS_ROLE = self::TRANSLATION_KEY_PREFIX . 'has_role';
    public const TRANSLATION_KEY_HAS_OWNERSHIP = self::TRANSLATION_KEY_PREFIX . 'has_ownership';
    public const TRANSLATION_KEY_HAS_PRIVILEGES_TO_RESOURCE = self::TRANSLATION_KEY_PREFIX . 'has_privileges_to_resource';
    public const TRANSLATION_KEY_HAS_PRIVILEGES_TO_ACTION = self::TRANSLATION_KEY_PREFIX . 'has_privileges_to_action';
    public const TRANSLATION_KEY_INVALID_ACTOR = self::TRANSLATION_KEY_PREFIX . 'invalid_actor';

    public const TRANSLATION_PARAMETERS = [
        // always
        '{actorId}',
        '{actorType}',

        // based on method
        '{action}',
        '{expectedActorType}',
        '{requiredRoles}',
        '{resource}',
        '{resourceId}',
    ];

    /**
     * @param array<string, string> $translationParameters
     */
    private function __construct(
        private string $translationKey,
        private array $translationParameters,
        string $message,
        int $code = 0,
        ?Throwable $previous = null,
    ) {
        parent::__construct($message, $code, $previous);
    }

    /**
     * @param class-string<Actor> $expectedActorClass
     */
    public static function checkActorInstanceOf(Actor $actor, string $expectedActorClass, ?string $action = null): void
    {
        if (!$actor instanceof $expectedActorClass) {
            if ($action && class_exists($action, false)) {
                $action = (new ReflectionClass($action))->getShortName();
            }

            $message = $actor::type() . ' has no permission to perform ' . ($action ? '"' . $action . '" ' : '') . 
                'action. Action requires ' . $expectedActorClass::type() . ' privileges.';

            throw new self(
                self::TRANSLATION_KEY_INSTANCE_OF,
                [
                    '{action}' => $action,
                    '{actorId}' => $actor->id->toRfc4122(),
                    '{actorType}' => $actor::type(),
                    '{expectedActorType}' => $expectedActorClass::type(),
                ],
                $message,
                0,
                null,
            );
        }
    }

    /**
     * @param Role[] $requiredRoles
     */
    public static function checkActorHasRole(Actor $actor, array $requiredRoles, ?string $action = null): void
    {
        if (!in_array($actor->getRole(), $requiredRoles, true)) {
            if ($action && class_exists($action, false)) {
                $action = (new ReflectionClass($action))->getShortName();
            }

            $message = $actor::type() . ' has no permission to perform ' . ($action ? '"' . $action . '" ' : '') . 
                'action. Action requires "' . implode('", "', $requiredRoles) . '" role' . (count($requiredRoles) > 1 ? 's' : '') . '.';

            throw new self(
                self::TRANSLATION_KEY_HAS_ROLE,
                [
                    '{action}' => $action,
                    '{actorId}' => $actor->id->toRfc4122(),
                    '{actorType}' => $actor::type(),
                    '{requiredRoles}' => implode(', ', $requiredRoles),
                ],
                $message,
                0,
                null,
            );
        }
    }

    /**
     * @param null|class-string $resource
     */
    public static function checkActorHasOwnership(Actor $actor, Uuid $ownerId, ?string $resource = null, ?Uuid $resourceId = null): void
    {
        if (!$ownerId->equals($actor->id)) {
            if ($resource && class_exists($resource, false)) {
                $resource = (new ReflectionClass($resource))->getShortName();
            }

            $message = $actor::type() . ' has no ownership of ' . ($resource ? '"' . $resource . '"' : 'resource') . 
                ($resourceId ? ' with id: "' . $resourceId->toRfc4122() . '".' : '.');

            throw new self(
                self::TRANSLATION_KEY_HAS_OWNERSHIP,
                [
                    '{actorId}' => $actor->id->toRfc4122(),
                    '{actorType}' => $actor::type(),
                    '{resource}' => $resource,
                    '{resourceId}' => $resourceId?->toRfc4122(),
                ],
                $message,
                0,
                null,
            );
        }
    }

    /**
     * @param callable $callback which returns boolean and does not accept any arguments
     * @param null|class-string $resource
     */
    public static function checkActorHasPrivilegesToResource(Actor $actor, callable $callback, ?string $resource = null, ?Uuid $resourceId = null): void
    {
        if (!$callback()) {
            if ($resource && class_exists($resource, false)) {
                $resource = (new ReflectionClass($resource))->getShortName();
            }

            $message = $actor::type() . ' has no privileges to ' . ($resource ? '"' . $resource . '"' : 'resource') . 
                ($resourceId ? ' with id: "' . $resourceId->toRfc4122() . '".' : '.');

            throw new self(
                self::TRANSLATION_KEY_HAS_PRIVILEGES_TO_RESOURCE,
                [
                    '{actorId}' => $actor->id->toRfc4122(),
                    '{actorType}' => $actor::type(),
                    '{resource}' => $resource,
                    '{resourceId}' => $resourceId?->toRfc4122(),
                ],
                $message,
                0,
                null,
            );
        }
    }

    /**
     * @param callable $callback which returns boolean and does not accept any arguments
     */
    public static function checkActorHasPrivilegesToAction(Actor $actor, callable $callback, ?string $action = null): void
    {
        if (!$callback()) {
            if ($action && class_exists($action, false)) {
                $action = (new ReflectionClass($action))->getShortName();
            }

            $message = $actor::type() . ' has no privileges to perform action' . ($action ? ' "' . $action . '"' : '.');

            throw new self(
                self::TRANSLATION_KEY_HAS_PRIVILEGES_TO_ACTION,
                [
                    '{actorId}' => $actor->id->toRfc4122(),
                    '{actorType}' => $actor::type(),
                    '{action}' => $action,
                ],
                $message,
                0,
                null,
            );
        }
    }

    public static function becauseActorIsInvalid(Actor $actor): void
    {
        $message = $actor::type() . ' is invalid.';

        throw new self(
            self::TRANSLATION_KEY_INVALID_ACTOR,
            [
                '{actorId}' => $actor->id->toRfc4122(),
                '{actorType}' => $actor::type(),
            ],
            $message,
            0,
            null,
        );
    }

    /**
     * @return array{
     *      message: string,
     *      translationKey: string,
     *      translationParameters: array<string, string>,
     * }
     */
    public function jsonSerialize(): array
    {
        return [
            'message' => $this->message,
            'translationKey' => $this->translationKey,
            'translationParameters' => $this->translationParameters,
        ];
    }
}
