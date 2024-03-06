<?php declare( strict_types = 1 );

namespace Northrook\Symfony\Core\Facades;

use Northrook\Logger\Log;
use Northrook\Types\Path;
use Symfony\Component\DependencyInjection\Exception\ParameterNotFoundException;

final class Get extends AbstractFacade
{

	/**
	 * @var array<string, string> // [name => kernel.parameter]
	 */
	private static array $parameterCache = [];

	/**
	 * @var Path[]
	 */
	private static array $pathfinderCache = [];

	/**
	 * @param  string  ...$root  [0]  {@see ParameterBagInterface::get}
	 */
	public static function path( string ...$root ) : string {

		$path = Get::kernelParameter( array_shift( $root ) );

		$path = Get::pathfinder( $path, $root );

		return $path->value;
	}

	private static function kernelParameter( string $get ) : string {

		if ( !isset( self::$parameterCache[ $get ] ) ) {
			try {
				return self::$parameterCache[ $get ] = Get::kernel()->getContainer()->getParameter( $get );
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
	 * @param  string  $get
	 * @param  string[]|null  ...$root
	 * @return Path
	 */
	protected static function pathfinder( string $get, ?array ...$root ) : Path {

		if ( $root ) {
			$get .= implode( '/', $root );
		}

		if ( !isset( self::$pathfinderCache[ $get ] ) ) {
			$path = Path::type( $get );

			if ( $path->isValid ) {
				return self::$pathfinderCache[ $get ] = $path;
			}

			Log::Error(
				'Unable to resolve path {path}, the file or directory does not exist.',
				[
					'get'  => $get,
					'path' => $path,
				],
			);

			return $path;

		}

		return self::$pathfinderCache[ $get ];
	}

}