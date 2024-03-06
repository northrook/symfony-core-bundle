<?php

namespace Northrook\Symfony\Core\DependencyInjection;

use Northrook\Logger\Log;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * @internal
 * @author Martin Nielsen <mn@northrook.com>
 *
 */
final class ContainerInstance
{
	private static ContainerInterface $container;

	/**
	 * Assign the container instance and service locator.
	 *
	 * @param  ContainerInterface  $container
	 * @return void
	 */
	public static function set( ContainerInterface $container ) : void {

		if ( isset( self::$container ) ) {
			Log::Alert(
				'Attempting to override existing instance of {instance}. This is not allowed.',
				[
					'instance' => 'FacadesContainerInstance',
					'file'     => __FILE__,
					'class'    => self::class,
				],
			);
			return;
		}

		self::$container = $container;

	}

	/**
	 * @return ContainerInterface
	 */
	public static function get() : ContainerInterface {
		if ( !isset( self::$container ) ) {
			trigger_error( 'Container not set.', E_USER_ERROR );
		}
		return self::$container;
	}


}