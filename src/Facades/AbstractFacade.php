<?php

namespace Northrook\Symfony\Core\Facades;

use Exception;
use Northrook\Symfony\Core\DependencyInjection\FacadesContainerInstance;
use Northrook\Symfony\Core\Services\CurrentRequestService;
use Northrook\Symfony\Core\Services\EnvironmentService;
use Northrook\Symfony\Core\Services\PathfinderService;
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

	protected static function environment() : EnvironmentService {
		return self::getContainerService( 'core.service.environment' );
	}

	protected static function currentRequest() : CurrentRequestService {
		return self::getContainerService( 'core.service.request' );
	}

	protected static function pathfinder() : PathfinderService {
		return self::getContainerService( 'core.service.pathfinder' );
	}

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
			return null;
		}

		return $service;
	}

	private static function getContainer() : ContainerInterface {
		return FacadesContainerInstance::getContainer();
	}
}