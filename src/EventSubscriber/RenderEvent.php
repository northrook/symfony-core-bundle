<?php

declare( strict_types = 1 );

namespace Northrook\Symfony\Core\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;


/**
 * @author  Martin Nielsen <mn@northrook.com>
 */
final readonly class RenderEvent implements EventSubscriberInterface
{

    public function __construct(
            private ?FlashBagInterface $flashbag,
    )
    {
        dump( $this );
    }

    public function kernelResponseEvent() : void
    {
        dump( 'kernelResponseEvent', $this->flashbag );
    }

    public function kernelViewEvent() : void
    {
        dump( 'kernelViewEvent', $this->flashbag );
    }

    public static function getSubscribedEvents() : array
    {
        return [
                'kernel.response' => 'kernelResponseEvent',
                'kernel.view'     => 'kernelViewEvent',
        ];
    }
}