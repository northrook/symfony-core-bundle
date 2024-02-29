<?php

namespace Northrook\Symfony\Core\EventSubscriber;

use DateTimeInterface;
use Northrook\Support\Debug;
use Psr\Log\LoggerInterface;
use ReflectionClass;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Log\DebugLoggerInterface;
use Symfony\Component\HttpKernel\Log\Logger;

final class LogAggregationOnTerminateSubscriber implements EventSubscriberInterface
{

	public function __construct(
		private readonly ?Logger      $logger = null,
	) {}

	public function logAggregation() : void {

		$logs = [];

		foreach ( Debug::getLogs() as $log ) {

			$logs[] = [
				'channel'           => Debug::class,
				'context'           => $log->dump,
				'message'           => $log->message,
				'priority'          => $log->level->value,
				'priorityName'      => $log->level->name(),
				'timestamp'         => $log->timestamp->timestamp,
				'timestamp_rfc3339' => $log->timestamp->format( DateTimeInterface::RFC3339 ),
			];
		}


		$logger = new ReflectionClass( $this->logger );

//		$logger->getProperty( 'logs' )->setAccessible( true );

		$logger->getProperty( 'logs' )->setValue( $this->logger, $logs );

		dd( $this->logger );
	}

	public static function getSubscribedEvents() : array {
		return [
			'kernel.terminate' => 'logAggregation',
		];
	}
}