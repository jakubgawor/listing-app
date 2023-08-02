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
            $event->getRequest()->getSession()->getFlashBag()->add('error', $exception->getMessage());

            $response = new RedirectResponse('/');
            $event->setResponse($response);
        }

        if($exception instanceof RepeatedVerificationException) {
            $event->getRequest()->getSession()->getFlashBag()->add('notification', $exception->getMessage());

            $response = new RedirectResponse('/');
            $event->setResponse($response);
        }
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::EXCEPTION => 'onKernelException',
        ];
    }


}