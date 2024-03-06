<?php

namespace Northrook\Symfony\Core\Facades;

use Northrook\Logger\Log;
use Northrook\Symfony\Core\DependencyInjection\FacadesContainerInstance;
use Northrook\Symfony\Core\Services\CurrentRequestService;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpKernel\KernelInterface;

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

	protected static function currentRequest() : CurrentRequestService {
		return self::getContainerService( 'core.service.request' );
	}

//	protected static function pathfinder() : PathfinderService {
//		return self::getContainerService( 'core.service.pathfinder' );
//	}

	protected static function parameterBag() : ParameterBagInterface {
		return self::getContainerService( 'parameter_bag' );
	}

	protected static function kernel() : ?KernelInterface {
		return self::getContainerService( 'kernel' );
	}


	/**
	 * @param  string  $get  {@see ParameterBagInterface::get}
	 * @return mixed
	 */
	protected static function getContainerService( string $get ) : mixed {

		try {
			$service = self::getContainer()->get( $get );
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

	private static function getContainer() : ContainerInterface {
		return FacadesContainerInstance::getContainer();
	}
}