<?php

namespace Northrook\Symfony\Core\EventSubscriber;

use Northrook\Support\Debug;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Log\DebugLoggerInterface;
use Symfony\Component\HttpKernel\Log\Logger;

final class LogAggregationOnTerminateSubscriber implements EventSubscriberInterface
{

	public function __construct(
		private readonly ?Logger      $logger = null,
	) {}

	public function logAggregation() : void {


		foreach ( Debug::getLogs() as $log ) {

			$level = strtolower( $log->level->name() );


//			$this->logger->log();

			$this->logger->$level(
				$log->message,
				[
					'timestamp' => $log->timestamp,
					'dump'      => $log->dump,
				],
			);
		}


//		dd( $this );
	}

	public static function getSubscribedEvents() : array {
		return [
			'kernel.terminate' => 'logAggregation',
		];
	}
}