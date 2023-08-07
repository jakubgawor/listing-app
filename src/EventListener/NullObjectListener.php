<?php

namespace App\EventListener;

use App\Exception\ObjectNotFoundException;
use Symfony\Component\HttpKernel\Event\ControllerArgumentsEvent;

class NullObjectListener
{
    public function __invoke(ControllerArgumentsEvent $event): void
    {
        foreach ($event->getArguments() as $argument) {
            if ($argument === null) {
                throw new ObjectNotFoundException();
            }
        }
    }
}