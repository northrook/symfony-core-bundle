<?php

namespace Northrook\Symfony\Core\Traits;

use Northrook\Logger\Log;
use Northrook\Symfony\Core\App;
use Northrook\Symfony\Core\Get;
use Northrook\Types\Path;

trait StaticPathfinderTrait
{

	/**
	 * @var Path[] // Only valid Paths will be cached
	 */
	private static array $pathfinderCache = [];

	/**
	 * @param  string  $get  {@see ParameterBagInterface::get}
	 * @param  string|null  $path
	 * @return string
	 */
	public static function pathfinder( string $get, ?string $path = null ) : string {

		$get = App::getParameter( $get );
		$path = self::staticPathfinderResolver( $get, $path );

		return $path->value;
	}


	/**
	 * @param  string  $root
	 * @param  string|null  $path
	 * @return Path
	 */
	private static function staticPathfinderResolver( string $root, ?string $path ) : Path {

		$key = $root . ( $path ? '/' . $path : '' );

		if ( !isset( static::$pathfinderCache[ $key ] ) ) {

			$pathfinder = Path::type( $root );
			$pathfinder->add( $path );

			if ( $pathfinder->isValid ) {
				return self::$pathfinderCache[ $key ] = $pathfinder;
			}

			Log::Error(
				'Unable to resolve path {path}, the file or directory does not exist. The returned {type::class} is invalid.',
				[
					'cacheKey'    => $key,
					'path'        => $pathfinder->value,
					'type'        => $pathfinder,
					'type::class' => $pathfinder::class,
					'cache'       => self::$pathfinderCache,
				],
			);

			return $pathfinder;

		}

		return self::$pathfinderCache[ $key ];
	}

}