<?php

namespace Northrook\Symfony\Core\EventSubscriber;

use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class LogAggregationOnTerminateSubscriber implements EventSubscriberInterface
{

	public function logAggregation( LoggerInterface $logger ) : void {
		$logger->info( '{Terminated}', [ 'Terminated' => 'Terminated' ] );
	}

	public static function getSubscribedEvents() : array {
		return [
			'kernel.terminate' => 'logAggregation',
		];
	}
}