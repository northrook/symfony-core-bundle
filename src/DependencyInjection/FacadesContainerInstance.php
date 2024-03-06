<?php

namespace Northrook\Symfony\Core\DependencyInjection;

use Northrook\Logger\Log;
use Symfony\Component\DependencyInjection\ServiceLocator;
use Symfony\Component\DependencyInjection\ContainerInterface;

final class FacadesContainerInstance
{
	private static ContainerInterface $container;
	private static ?ServiceLocator    $locator;

	/**
	 * Assign the container instance and service locator.
	 *
	 * @param  ContainerInterface  $container
	 * @return void
	 */
	public static function setContainer( ContainerInterface $container ) : void {

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
		self::$locator = $container->get(
			id              : 'core.facades.locator',
			invalidBehavior : ContainerInterface::NULL_ON_INVALID_REFERENCE,
		);
	}

	/**
	 * @return ContainerInterface
	 */
	public static function getContainer() : ContainerInterface {
		if ( !isset( self::$container ) ) {
			trigger_error( 'Container not set.', E_USER_ERROR );
		}
		return self::$container;
	}

	public static function getLocator() : ServiceLocator {
		if ( self::$locator === null ) {
			trigger_error( 'Locator not set.', E_USER_ERROR );
		}
		return self::$locator;
	}


}