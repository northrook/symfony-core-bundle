<?php

declare( strict_types = 1 );

namespace Northrook\Symfony\Core\EventSubscriber;

use Northrook\Cache\MemoizationCache;
use Northrook\Logger\Log;
use Psr\Log\LoggerInterface;
use Symfony\Component\Cache\Adapter\PhpFilesAdapter;
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
            private PhpFilesAdapter  $memoizationCache,
            private ?LoggerInterface $logger = null,
    ) {}

    public function __invoke( RequestEvent $event ) : void
    {
        new MemoizationCache( $this->memoizationCache );
        Log::setLogger( $this->logger );
    }
}