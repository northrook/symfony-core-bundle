<?php

declare( strict_types = 1 );

namespace Northrook\Symfony\Core\DependencyInjection;

use Northrook\Cache\MemoizationCache;
use Northrook\Logger\Log;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ServiceLocator;
use Symfony\Component\HttpKernel\Event\RequestEvent;


/**
 *
 *
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
final readonly class ApplicationInitializer
{

    public function __construct(
            MemoizationCache         $memoizationCache,
            private ServiceLocator   $serviceLocator,
            private ?LoggerInterface $logger = null,
    ) {}

    public function __invoke( RequestEvent $event ) : void
    {
        new ServiceContainer( $this->serviceLocator );
        Log::setLogger( $this->logger );
    }
}