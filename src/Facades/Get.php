<?php

namespace Northrook\Symfony\Core\Facades;

use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

final class Get extends AbstractFacade
{

	/**
	 * @throws ContainerExceptionInterface
	 * @throws NotFoundExceptionInterface
	 */
	public static function path( string $path ) : string {

		$root = self::getContainer()->get('dir.root') . '/' . $path;

		return $path;
	}

}