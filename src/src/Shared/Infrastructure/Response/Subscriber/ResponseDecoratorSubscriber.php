<?php

declare(strict_types=1);

namespace App\Shared\Infrastructure\Response\Subscriber;

use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

final class ResponseDecoratorSubscriber implements EventSubscriberInterface
{
    public function __construct(
        #[Autowire(env: 'APP_VERSION')]
        private string $appVersion,

        #[Autowire(env: 'APP_COMMIT_SHA')]
        private string $appCommitSha,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::RESPONSE => 'onKernelResponse',
        ];
    }

    public function onKernelResponse(ResponseEvent $event): void
    {
        $response = $event->getResponse();
        $response->headers->set('X-Version', $this->appVersion);
        $response->headers->set('X-Version-Sha', $this->appCommitSha);
    }
}
