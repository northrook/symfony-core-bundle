<?php

declare( strict_types = 1 );

namespace Northrook\Symfony\Core\EventSubscriber;

use Northrook\Cache\MemoizationCache;
use Northrook\Logger\Log;
use Northrook\Symfony\Core\DependencyInjection\ServiceContainer;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ServiceLocator;
use Symfony\Component\HttpKernel\Event\RequestEvent;


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
final readonly class CoreServiceInitializer
{

    public function __construct(
            private MemoizationCache $memoizationCache,
            private ServiceLocator   $serviceLocator,
            private ?LoggerInterface $logger = null,
    ) {}

    public function __invoke( RequestEvent $event ) : void
    {
        new ServiceContainer( $this->serviceLocator );
        Log::setLogger( $this->logger );
    }
}