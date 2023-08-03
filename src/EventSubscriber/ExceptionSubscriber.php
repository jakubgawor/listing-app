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

        $exceptionClassMap = [
            ListingNotFoundException::class => ['flashType' => 'error', 'path' => '/'],
            UnauthorizedAccessException::class => ['flashType' => 'error', 'path' => '/'],
            RepeatedVerificationException::class => ['flashType' => 'notification', 'path' => '/'],
        ];

        foreach ($exceptionClassMap as $exceptionClass => $details) {
            if($exception instanceof $exceptionClass) {
                $this->handleException($event, $exception, $details['flashType'], $details['path']);
            }
        }

    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::EXCEPTION => 'onKernelException',
        ];
    }

    private function handleException(ExceptionEvent $event, \Throwable $exception, string $flashType, string $redirectPath): void
    {
        $event->getRequest()->getSession()->getFlashBag()->add($flashType, $exception->getMessage());
        $event->setResponse(new RedirectResponse($redirectPath));
    }
}