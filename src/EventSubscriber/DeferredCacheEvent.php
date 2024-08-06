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
final class DeferredCacheEvent implements EventSubscriberInterface
{
    private array $cacheAdapters;

    public function __construct(
        private readonly ?LoggerInterface $logger = null,
        AdapterInterface                  ...$cacheAdapters
    ) {
        $this->cacheAdapters = $cacheAdapters;
    }

    public function enqueueAdapter( AdapterInterface $adapter ) : void {
        $this->cacheAdapters[] = $adapter;
    }

    public function persistDeferredCacheItems() : void {
        foreach ( $this->cacheAdapters as $adapter ) {
            dump( $adapter );
            $adapter->commit();
        }
        $this->logger?->info( "Successfully commited cache items." );
    }

    public static function getSubscribedEvents() : array {
        return [
            'kernel.finish_request' => 'persistDeferredCacheItems',
            // 'kernel.terminate' => 'persistDeferredCacheItems',
        ];
    }
}