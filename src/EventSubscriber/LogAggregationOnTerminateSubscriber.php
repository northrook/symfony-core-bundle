<?php

namespace Northrook\Symfony\Core\EventSubscriber;

use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

final class LogAggregationOnTerminateSubscriber implements EventSubscriberInterface
{

	public function __construct(
		private ?LoggerInterface $logger = null,
	) {}

	public function logAggregation() : void {

		dd( $this, );
	}

	public static function getSubscribedEvents() : array {
		return [
			'kernel.terminate' => 'logAggregation',
		];
	}
}