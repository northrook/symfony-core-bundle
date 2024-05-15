<?php

namespace Northrook\Symfony\Core\EventSubscriber;

use Northrook\Logger\Log;
use Northrook\Logger\Timer;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Log\Logger;

/**
 * Collects and aggregates and logs when:
 *
 * * Finishing a request.
 *
 * * {@see Logger} - Symfony's internal logger
 * > Part of the $container.
 *
 * * {@see Log} - Logger service by Northrook
 * > Static session logs.
 *
 * @version 1.0.0 ✅
 * @author  Martin Nielsen <mn@northrook.com>
 */
final readonly class LogAggregationSubscriber implements EventSubscriberInterface
{

    public function __construct(
        private ?Logger $logger = null,
    ) {
        Timer::start( 'log_aggregation' );
    }

    public function logAggregation() : void {

        $loggerStartCount = count( $this->logger->getLogs() );
        $log              = Log::inventory();

        foreach ( $log as $entry ) {
            $this->logger->log(
                strtolower( $entry->Level->name() ),
                $entry->message,
                $entry->context ?? [],
            );
        }

        $this->logger->info(
            "Log aggregation completed in {time}.",
            [
                'time'               => Timer::get( 'log_aggregation' ) . 'ms',
                Log\Entry::class     => count( $log ),
                $this->logger::class => $loggerStartCount,
                'total'              => count( $this->logger->getLogs() ),
            ],
        );
    }

    public static function getSubscribedEvents() : array {
        return [
            'kernel.finish_request' => 'logAggregation',
        ];
    }
}