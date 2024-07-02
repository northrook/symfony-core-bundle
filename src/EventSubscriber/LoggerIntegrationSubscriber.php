<?php

namespace Northrook\Symfony\Core\EventSubscriber;

use Northrook\Logger\Log;
use Psr\Log\LoggerInterface;
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
final readonly class LoggerIntegrationSubscriber implements EventSubscriberInterface
{

    public function __construct( private ?LoggerInterface $logger = null ) {}

    public function initializeLogger() : void {
        Log::setLogger( $this->logger );
    }

    public static function getSubscribedEvents() : array {
        return [
            'kernel.request' => 'initializeLogger',
        ];
    }
}