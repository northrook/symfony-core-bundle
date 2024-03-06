<?php declare( strict_types = 1 );

namespace Northrook\Symfony\Core;

use Northrook\Logger\Log;
use Northrook\Types\Path;
use Symfony\Component\DependencyInjection\Exception\ParameterNotFoundException;

final class Get extends Facades\AbstractFacade
{
	/**
	 * @var array<string, string> // [name => kernel.parameter]
	 */
	private static array $parameterCache = [];

	/**
	 * @var Path[] // Only valid Paths will be cached
	 */
	private static array $pathfinderCache = [];


	/**
	 * @param  string  $root  {@see ParameterBagInterface::get}
	 * @param  string|null  $path
	 * @return string
	 */
	public static function path( string $root, ?string $path = null ) : string {

		$root = Get::kernelParameter( $root );
		$path = Get::pathfinder( $root, $path );

		return $path->value;
	}

	private static function kernelParameter( string $get ) : string {

		if ( !isset( self::$parameterCache[ $get ] ) ) {
			try {
				return self::$parameterCache[ $get ] = static::kernel()->getContainer()->getParameter( $get );
			}
			catch ( ParameterNotFoundException $e ) {
				Log::Alert(
					'Failed getting parameter {get}, the parameter does not exist. Returned raw string:{get} instead.',
					[
						'get'       => $get,
						'exception' => $e,
					],
				);
				return $get;
			}
		}

		return self::$parameterCache[ $get ];
	}

	/**
	 * @param  string  $root
	 * @param  string|null  $path
	 * @return Path
	 */
	protected static function pathfinder( string $root, ?string $path ) : Path {

		$key = $root . ( $path ? '/' . $path : '' );

		if ( !isset( self::$pathfinderCache[ $key ] ) ) {

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