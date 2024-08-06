<?php

declare( strict_types = 1 );

namespace Northrook\Symfony\Core\EventSubscriber;

use Symfony\Component\Cache\Adapter\AdapterInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * @author  Martin Nielsen <mn@northrook.com>
 */
final readonly class DeferredCacheEvent implements EventSubscriberInterface
{
    private array $cacheAdapters;

    public function __construct( AdapterInterface ...$cacheAdapters ) {
        $this->cacheAdapters = $cacheAdapters;
    }

    public function persistDeferredCacheItems() : void {
        foreach ( $this->cacheAdapters as $adapter ) {
            $adapter->commit();
        }
    }

    public static function getSubscribedEvents() : array {
        return [
            'kernel.terminate' => 'persistDeferredCacheItems',
        ];
    }
}