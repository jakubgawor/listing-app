<?php

namespace App\EventSubscriber;

use App\Exception\AdminDegradationException;
use App\Exception\AdminDeletionException;
use App\Exception\AdminPromotionException;
use App\Exception\BanUserException;
use App\Exception\NotVerifiedListingException;
use App\Exception\RepeatedVerificationException;
use App\Exception\UnauthorizedAccessException;
use App\Exception\ObjectNotFoundException;
use App\Exception\UserNotRegisteredException;
use App\Exception\VerifyEmailException;
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
            UnauthorizedAccessException::class => ['flashType' => 'error', 'path' => '/'],
            RepeatedVerificationException::class => ['flashType' => 'notification', 'path' => '/'],
            AdminDeletionException::class => ['flashType' => 'error', 'path' => '/'],
            AdminPromotionException::class => ['flashType' => 'notification', 'path' => '/'],
            AdminDegradationException::class => ['flashType' => 'notification', 'path' => '/'],
            ObjectNotFoundException::class => ['flashType' => 'error', 'path' => '/'],
            BanUserException::class => ['flashType' => 'error', 'path' => '/'],
            UserNotRegisteredException::class => ['flashType' => 'notification', 'path' => '/register'],
            VerifyEmailException::class => ['flashType' => 'error', 'path' => '/'],
            NotVerifiedListingException::class => ['flashType' => 'error', 'path' => '/'],
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