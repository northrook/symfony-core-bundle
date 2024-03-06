<?php

namespace Northrook\Symfony\Core\Facades;

use Exception;
use Northrook\Symfony\Core\DependencyInjection\FacadesContainerInstance;
use Northrook\Symfony\Core\Services\CurrentRequestService;
use Northrook\Symfony\Core\Services\PathfinderService;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;

abstract class AbstractFacade
{

	protected static function currentRequest() : CurrentRequestService {
		return self::getContainerService( 'core.service.request' );
	}

	protected static function pathfinder() : PathfinderService {
		return self::getContainerService( 'core.service.pathfinder' );
	}

	/**
	 * @param  string  $get  {@see ParameterBagInterface::get}
	 * @return mixed
	 */
	private static function getContainerService( string $get ) : mixed {

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