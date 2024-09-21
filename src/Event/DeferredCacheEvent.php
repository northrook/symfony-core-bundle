<?php

declare( strict_types = 1 );

namespace Northrook\Symfony\Core\Event;

use Symfony\Component\Cache\Adapter\AdapterInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\TerminateEvent;


/**
 * @author  Martin Nielsen <mn@northrook.com>
 */
final readonly class DeferredCacheEvent implements EventSubscriberInterface
{
    private array $cacheAdapters;

    public function __construct( AdapterInterface ...$cacheAdapters )
    {
        $this->cacheAdapters = $cacheAdapters;
    }

    public function persistDeferredCacheItems( TerminateEvent $event ) : void
    {
        foreach ( $this->cacheAdapters as $adapter ) {
            $adapter->commit();
        }
    }

    public static function getSubscribedEvents() : array
    {
        return [
                'kernel.terminate' => 'persistDeferredCacheItems',
        ];
    }
}