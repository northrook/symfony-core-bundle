<?php

namespace Northrook\Symfony\Core\EventSubscriber;

use DateTimeInterface;
use Northrook\Logger\Log;
use Northrook\Logger\Timer;
use ReflectionClass;
use ReflectionException;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Log\Logger;

/**
 * Collects and aggregates and logs on application termination.
 *
 * * {@see Logger} - Symfony's internal logger
 * > Part of the $container.
 *
 * * {@see Log} - Logger service by Northrook
 * > Static session logs.
 *
 * @version 1.0.0 ✅
 * @author Martin Nielsen <mn@northrook.com>
 */
final class LogAggregationOnTerminateSubscriber implements EventSubscriberInterface
{

	private array          $log = [];
	private readonly array $loggerLogs;

	public function __construct(
		private readonly ?Logger $logger = null,
	) {
		Timer::start( 'log_aggregation' );
		$this->loggerLogs = $this->logger->getLogs();
	}

	public function logAggregation() : void {

		foreach ( Log::inventory() as $log ) {
			$this->log[] = [
				'channel'           => null,
				'context'           => $log->dump,
				'message'           => $log->message,
				'priority'          => (int) $log->Level->value ?? 100,
				'priorityName'      => strtolower( $log->Level->name() ),
				'timestamp'         => (int) $log->Timestamp->timestamp,
				'timestamp_rfc3339' => $log->Timestamp->format( DateTimeInterface::RFC3339 ),
			];
		}

		try {
			( new ReflectionClass( $this->logger ) )
				->getProperty( 'logs' )
				->setValue(
					$this->logger,
					[ array_merge( $this->log, $this->loggerLogs ) ],
				)
			;

			$this->logger->info(
				"Log aggregation completed in {time}.",
				[
					'time'               => Timer::get( 'log_aggregation' ) . 'ms',
					Log\Entry::class     => count( $this->log ),
					$this->logger::class => count( $this->loggerLogs ),
					'total'              => count( $this->logger->getLogs() ),
				],
			);
		}
		catch ( ReflectionException $e ) {
			$this->logger->error(
				"Unable to merge logs: {message}",
				[
					'message'            => $e->getMessage(),
					'time'               => Timer::get( 'log_aggregation' ) . 'ms',
					Log\Entry::class     => count( $this->log ),
					$this->logger::class => count( $this->loggerLogs ),
				],
			);

		}

	}

	public static function getSubscribedEvents() : array {
		return [
			'kernel.terminate' => 'logAggregation',
		];
	}
}