<?php

namespace Northrook\Symfony\Core\Facades;

final class Get extends AbstractFacade
{

	/**
	 */
	public static function path( string $path ) : string {

		$pathfinder = self::pathfinder()->get( 'dir.public' ) . $path;

		return $pathfinder;
	}

}