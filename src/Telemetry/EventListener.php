<?php

declare( strict_types = 1 );

namespace Northrook\Symfony\Core\Telemetry;

use Northrook\Symfony\Core\Facade\Stopwatch;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;


final readonly class EventListener implements EventSubscriberInterface
{
    public function __construct()
    {
        Stopwatch::start( __CLASS__, 'telemetry' );
    }

    public static function getSubscribedEvents() : array
    {
        return [
                'kernel.request' => 'onKernelRequest',
        ];
    }

    public function onKernelRequest(
            RequestEvent $event,
    ) : void
    {
        Stopwatch::start( 'onKernelRequest', 'telemetry' );
    }
}