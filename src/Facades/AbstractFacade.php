<?php

namespace Northrook\Symfony\Core\Facades;

use Northrook\Logger\Log;
use Northrook\Symfony\Core\DependencyInjection\ContainerInstance;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;
use Symfony\Component\DependencyInjection\Exception\ParameterNotFoundException;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpKernel as App;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Stopwatch\Stopwatch;

/**
 * @internal
 *
 * @author Martin Nielsen <mn@northrook.com>
 *
 */
abstract class AbstractFacade
{
	protected const KERNEL_DIR = [
		'root',          // ~symfony/
		'assets',        // ~symfony/assets/
		'public',        // ~symfony/public/
		'public.assets', // ~symfony/public/assets/
		'templates',     // ~symfony/templates/
		'cache',         // ~symfony/cache/
		'logs',          // ~symfony/logs/
	];

	/**
	 * @param  ?string  $get  {@see ParameterBagInterface::get}
	 *
	 * @return ParameterBagInterface | string | null
	 */
	protected static function parameterBag( ?string $get = null ) : ParameterBagInterface | string | null {

		$parameterBag = static::getContainerService( 'kernel' )->getContainer()->getParameterBag();

		if ( null === $get ) {
			return $parameterBag;
		}

		try {
			return $parameterBag->get( $get );
		}
		catch ( ParameterNotFoundException $exception ) {
			Log::Alert(
				'Failed getting parameter {get}, the parameter does not exist. Returned raw string:{get} instead.',
				[
					'get'       => $get,
					'exception' => $exception,
				],
			);
			return $get;
		}
	}

	/**
	 * Returns the {@see Stopwatch} instance from the {@see ContainerInstance}.
	 *
	 * @return ?Stopwatch {@see Stopwatch}, or null if the service is not available.
	 */
	protected static function stopwatch() : ?Stopwatch {
		return self::getContainerService( 'debug.stopwatch' );
	}

	/**
	 * Returns the {@see App\Kernel} instance from the {@see ContainerInstance}.
	 *
	 * @return KernelInterface {@see App\Kernel}
	 */
	protected static function kernel() : KernelInterface {
		return self::getContainerService( 'kernel' );
	}

	/**
	 * @param string  $get  {@see ParameterBagInterface::get}
	 *
	 * @return mixed
	 */
	private static function getContainerService( string $get ) : mixed {

		try {
			$service = self::ContainerInstance()->get( $get );
		}
		catch ( NotFoundExceptionInterface | ContainerExceptionInterface $e ) {
			Log::Alert(
				'Failed getting container parameter {get}, the parameter does not exist. Returned {return} instead.',
				[
					'get'       => $get,
					'return'    => 'null',
					'exception' => $e,
				],
			);
			return null;
		}

		return $service;
	}

	private static function ContainerInstance() : ContainerInterface {
		return ContainerInstance::get();
	}
}