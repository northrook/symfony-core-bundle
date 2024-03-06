<?php

namespace Northrook\Symfony\Core\DependencyInjection;

use Northrook\Logger\Log;
use Symfony\Component\DependencyInjection\ContainerInterface;

final class FacadesContainerInstance
{
	private static ?self $instance = null;

	private function __construct(
		private ContainerInterface $container,
	) {}

	public static function setContainer( ContainerInterface $container ) : void {
		if ( self::$instance !== null ) {
			Log::Alert(
				'Attempting to override existing instance of {instance}. This is not allowed.',
				[
					'instance' => 'FacadesContainerInstance',
					'file'     => __FILE__,
					'class'    => FacadesContainerInstance::class,
				],
			);
			return;
		}
		self::$instance = new self( $container );
	}

	/**
	 * @return ContainerInterface
	 */
	public static function getContainer() : ContainerInterface {
		if ( self::$instance === null ) {
			trigger_error( 'Instance not set.', E_USER_ERROR );
		}
		return self::$instance->container;
	}


}