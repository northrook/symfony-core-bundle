<?php

declare( strict_types = 1 );

namespace Northrook\Symfony\Core\EventSubscriber;

use Northrook\Symfony\Core\Service\CurrentRequest;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;


/**
 * @author  Martin Nielsen <mn@northrook.com>
 */
final readonly class RenderEvent implements EventSubscriberInterface
{

    public function __construct(
            private CurrentRequest $request,
    )
    {
        dump( $this );
    }

    public function kernelResponseEvent() : void
    {
        dump( 'kernelResponseEvent', $this->request );
    }

    public function kernelViewEvent() : void
    {
        dump( 'kernelViewEvent', $this->request );
    }

    public static function getSubscribedEvents() : array
    {
        return [
                'kernel.response' => 'kernelResponseEvent',
                'kernel.view'     => 'kernelViewEvent',
        ];
    }
}