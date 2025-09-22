<?php

declare(strict_types=1);

namespace App\Shared\Infrastructure\Response\Subscriber;

use App\Shared\Application\Security\Exception\AccessDeniedException;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Security\Core\Exception\AccessDeniedException as SecurityAccessDeniedException;
use Throwable;

final readonly class AccessExceptionSubscriber implements EventSubscriberInterface
{
    public function __construct(
        #[Autowire(param: 'kernel.debug')]
        private bool $debug,
    ) {
    }

    public function onKernelException(ExceptionEvent $event): void
    {
        $exception = $event->getThrowable();
        $class = $exception::class;
        if (!in_array(
            $class,
            [AccessDeniedException::class, HttpException::class, UnauthorizedHttpException::class, SecurityAccessDeniedException::class],
        ) || $event->getResponse()) {
            return;
        }

        if (str_contains($exception->getMessage(), 'Full authentication is required')) {
            $class = UnauthorizedHttpException::class;
        }

        /** @var AccessDeniedException|HttpException|UnauthorizedHttpException|SecurityAccessDeniedException $exception */
        $statusCode = match ($class) {
            AccessDeniedException::class => Response::HTTP_FORBIDDEN,
            /* @phpstan-ignore-next-line */
            HttpException::class => $exception->getStatusCode(),
            UnauthorizedHttpException::class => Response::HTTP_UNAUTHORIZED,
            SecurityAccessDeniedException::class => Response::HTTP_FORBIDDEN,
        };

        $model = match ($class) {
            /* @phpstan-ignore-next-line */
            AccessDeniedException::class => $exception->jsonSerialize(),
            HttpException::class, UnauthorizedHttpException::class, SecurityAccessDeniedException::class => $this->debug
                ? ['message' => $exception->getMessage(), 'debug' => array_filter([
                    'exception' => $class,
                    'placement' => sprintf('%s:%d', $exception->getFile(), $exception->getLine()),
                    'trace' => $exception->getTrace(),
                    'previous' => $exception->getPrevious() instanceof Throwable
                        ? [
                            'message' => $exception->getPrevious()->getMessage(),
                            'exception' => $exception->getPrevious()::class,
                            'placement' => sprintf('%s:%d', $exception->getPrevious()->getFile(), $exception->getPrevious()->getLine()),
                            'trace' => $exception->getPrevious()->getTrace(),
                        ]
                        : null,
                ])]
                : ['message' => UnauthorizedHttpException::class === $class ? 'Authentication required.' : 'Access denied.'],
        };

        $event->setResponse(new JsonResponse($model, $statusCode));
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::EXCEPTION => ['onKernelException', 0], // must be less than 1
        ];
    }
}
