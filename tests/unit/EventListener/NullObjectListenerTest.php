<?php

namespace App\Tests\unit\EventListener;

use App\EventListener\NullObjectListener;
use App\Exception\ObjectNotFoundException;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\ControllerArgumentsEvent;
use Symfony\Component\HttpKernel\KernelInterface;

class NullObjectListenerTest extends TestCase
{
    /** @test */
    public function invoke_with_non_null_args()
    {
        $listener = new NullObjectListener();

        $event = new ControllerArgumentsEvent(
            $this->createMock(KernelInterface::class),
            function () {},
            ['argument1', 'argument2'],
            $this->createMock(Request::class),
            KernelInterface::MAIN_REQUEST
        );

        $listener($event);

        $this->expectNotToPerformAssertions();
    }

    /** @test */
    public function invoke_with_null_arg()
    {
        $listener = new NullObjectListener();

        $event = new ControllerArgumentsEvent(
            $this->createMock(KernelInterface::class),
            function () {},
            [null],
            $this->createMock(Request::class),
            KernelInterface::MAIN_REQUEST
        );

        $this->expectException(ObjectNotFoundException::class);

        $listener($event);
    }
}