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

    /**
     * @param null|LoggerInterface  $logger
     * @param AdapterInterface|array{
     *         array{key,AdapterInterface}
     *     }                        ...$cacheAdapters
     */
    public function __construct(
        private ?LoggerInterface                    $logger = null,
        AdapterInterface | array                    ...$cacheAdapters
        // AdapterInterface         $latte,
        // AdapterInterface         $memoization,
        // AdapterInterface         $pathfinder,
    ) {
        foreach ( $cacheAdapters as $index => $service ) {
            unset( $cacheAdapters[ $index ] );
            if ( \is_array( $service ) ) {
                $key = \reset( $service );
                if ( !\is_string( $key ) ) {
                    $key = $index;
                }

                $adapter = \end( $service );
                if ( !$adapter instanceof AdapterInterface ) {
                    throw new \InvalidArgumentException(
                        'The provided service must be an instance of AdapterInterface.',
                    );
                }
            }
            else {
                $key     = $index;
                $adapter = $service;
            }

            $cacheAdapters[ $key ] = $adapter;
        }
        $this->cacheAdapters = $cacheAdapters;
        dump( $this );
    }

    public function persistDeferredCacheItems() : void {
        foreach ( $this->cacheAdapters as $name => $adapter ) {
            if ( $adapter->commit() ) {
                $this->logger?->info(
                    "Commited deferred {name} cache items on {event}.",
                    [ 'name' => $name, 'event' => 'kernel.terminate' ],
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