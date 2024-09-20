<?php

declare( strict_types = 1 );

namespace Northrook\Symfony\Core\Telemetry;

use Northrook\Symfony\Core\Telemetry;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\TerminateEvent;


/**
 * @author Martin Nielsen <mn@northrook.com>
 */
final readonly class TelemetryEventListener implements EventSubscriberInterface
{
    const string GROUP = 'Clerk';

    public function __construct( private Telemetry\Clerk $monitor ) {}

    public static function getSubscribedEvents() : array
    {
        return [
                'kernel.request'              => 'onKernelRequest',
                'kernel.controller'           => 'onKernelController',
                'kernel.controller_arguments' => 'onKernelControllerArguments',
                'kernel.view'                 => 'onKernelView',
                'kernel.response'             => 'onKernelResponse',
                'kernel.finish_request'       => 'onKernelFinishRequest',
                'kernel.exception'            => 'onKernelException',
                'kernel.terminate'            => 'onKernelTerminate',
        ];
    }

    public function onKernelRequest() : void
    {
        $this->monitor->event( 'onKernelRequest', $this::GROUP );
    }

    public function onKernelController() : void
    {
        $this->monitor->event( 'onKernelController', $this::GROUP );
        $this->monitor->stop( 'onKernelRequest', $this::GROUP );
    }

    public function onKernelControllerArguments() : void
    {
        $this->monitor->stop( 'onKernelController', $this::GROUP );
        // $this->monitor->event( 'onKernelControllerArguments', $this::GROUP );
    }

    public function onKernelView() : void
    {
        $this->monitor->event( 'onKernelView', $this::GROUP );
    }

    public function onKernelResponse() : void
    {
        $this->monitor->event( 'onKernelResponse', $this::GROUP );
    }

    public function onKernelFinishRequest() : void
    {
        $this->monitor->stop( 'onKernelResponse', $this::GROUP );
        $this->monitor->event( 'onKernelFinishRequest', $this::GROUP );
    }

    public function onKernelException() : void
    {
        $this->monitor->event( 'onKernelException', $this::GROUP );
    }

    public function onKernelTerminate( TerminateEvent $event ) : void
    {
        foreach ( $this->monitor->getEvents() as $event ) {
            $event->stop();
        }
    }

}