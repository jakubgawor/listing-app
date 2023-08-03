<?php

namespace App\EventSubscriber;

use App\Exception\ListingNotFoundException;
use App\Exception\RepeatedVerificationException;
use App\Exception\UnauthorizedAccessException;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class ExceptionSubscriber implements EventSubscriberInterface
{
    public function onKernelException(ExceptionEvent $event): void
    {
        $exception = $event->getThrowable();

        if($exception instanceof ListingNotFoundException || $exception instanceof UnauthorizedAccessException) {
            $this->handleException($event, $exception, 'error', '/');
        }

        if($exception instanceof RepeatedVerificationException) {
            $this->handleException($event, $exception, 'notification', '/');
        }
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::EXCEPTION => 'onKernelException',
        ];
    }

    private function handleException(ExceptionEvent $event, \Exception $exception, string $flashType, string $redirectPath): void
    {
        $event->getRequest()->getSession()->getFlashBag()->add($flashType, $exception->getMessage());
        $event->setResponse(new RedirectResponse($redirectPath));
    }
}