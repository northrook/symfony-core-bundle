<?php

namespace Northrook\Symfony\Core\EventSubscriber;

use Northrook\Support\Debug;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

final class LogAggregationOnTerminateSubscriber implements EventSubscriberInterface
{

	public function __construct(
		private readonly ?LoggerInterface $logger = null,
	) {}

	public function logAggregation() : void {


		foreach ( Debug::getLogs() as $log ) {

			$level = strtolower( $log->level->name() );

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