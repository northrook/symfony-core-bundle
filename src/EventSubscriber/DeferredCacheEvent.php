<?php

namespace Northrook\Symfony\Core\EventSubscriber;

use Northrook\Logger\Log;
use Psr\Log\LoggerInterface;
use Symfony\Component\Cache\Adapter\AdapterInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Pass the {@see LoggerInterface} instance into {@see Log} for static logging.
 *
 * * {@see LoggerInterface} - Symfony's internal logger
 * > Part of the $container.
 *
 * * {@see Log} - Logger service by Northrook
 * > Static session logs.
 *
 * @author  Martin Nielsen <mn@northrook.com>
 */
final readonly class DeferredCacheEvent implements EventSubscriberInterface
{
    private array $cacheAdapters;

    public function __construct(
        private ?LoggerInterface          $logger = null,
        AdapterInterface                  ...$cacheAdapters
    ) {
        $this->cacheAdapters = $cacheAdapters;
    }

    public function persistDeferredCacheItems() : void {
        foreach ( $this->cacheAdapters as $name => $adapter ) {
            if ( $adapter->commit() ) {
                $this->logger?->info(
                    "Commited deferred cache items on {event}.",
                    [ 'event' => 'kernel.terminate' ],
                );
            }
        }
    }

    public static function getSubscribedEvents() : array {
        return [
            'kernel.terminate' => 'persistDeferredCacheItems',
        ];
    }
}