<?php

namespace Northrook\Symfony\Core\Facades;

use Northrook\Symfony\Core\DependencyInjection\FacadesContainerInstance;
use Psr\Container\ContainerInterface;

abstract class AbstractFacade
{
	protected static function getContainer() : ContainerInterface {
		return FacadesContainerInstance::getContainer();
	}
}