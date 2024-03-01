<?php

namespace Northrook\Symfony\Core\Services;

use Northrook\Logger\Log;
use Symfony\Component\DependencyInjection\Exception\ParameterNotFoundException;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use UnitEnum;

/** Interact with environment variables
 *
 * * Run checks against environment status
 * * Get environment variables
 *
 * @var $env
 */
final class EnvironmentService
{

	public readonly bool $dev;
	public readonly bool $prod;
	public readonly bool $debug;

//	public static array $pathCache = [];

	public function __construct(
		private readonly ParameterBagInterface $parameter,
	) {

		$this->dev = $this->parameter->get( 'kernel.environment' ) == 'dev';
		$this->prod = $this->parameter->get( 'kernel.environment' ) == 'prod';
		$this->debug = $this->parameter->get( 'kernel.debug' ) === true;
//		if ( !isset( self::$pathCache[ 'dir.root' ] ) ) {
//			try {
//				self::$pathCache[ 'dir.root' ] = $this->get( 'kernel.project_dir' );
//			}
//			catch ( ParameterNotFoundException $exception ) {
//				$this->log->warning( $exception->getMessage() );
//			}
//		}
	}

	/** Get environment variable
	 *
	 * @param  null|string  $key
	 * @param  null|string  $default
	 * @param  bool  $logOnFail
	 * @return string|int|bool|array|float|UnitEnum|null
	 */
	public function get( ?string $key = null, ?string $default = null, bool $logOnFail = true,
	) : string | int | bool | array | null | float | UnitEnum {

		if ( null === $key ) {
			return $this->parameter->all();
		}

		try {
			return $this->parameter->get( $key );
		}
		catch ( ParameterNotFoundException $exception ) {
			if ( $logOnFail ) {
				Log::warning( $exception->getMessage(), [
					'exception' => $exception,
				] );
			}

			return $default ?? false;
		}
	}


	/** Check against or get environment variable
	 *
	 * * Pass a string to check against, returning true or false
	 * * Checks against kernel.environment and kernel.debug
	 *
	 * @param  string  $env
	 * @return bool
	 */
	public function is( string $env ) : bool {
		return $this->parameter->get( 'kernel.environment' ) == $env;
	}

//	public function path( string $key = null ) : string {
//
//		$key = str_replace( [ '/', '\\', '//', '\\\\' ], DIRECTORY_SEPARATOR, $key );
//
//		if ( array_key_exists( $key, $this::$pathCache ) ) {
//			return self::$pathCache[ $key ];
//		}
//
//		$root = self::$pathCache[ 'dir.root' ];
//
//		if ( array_key_exists( $key, self::ENVIRONMENT_PATHS ) ) {
//			$root .= self::ENVIRONMENT_PATHS[ $key ];
//		}
//		else {
//			$root .= DIRECTORY_SEPARATOR . $key;
//		}
//
//		$path = [];
//		$explode = array_filter( explode( '/', strtr( $root, '\\', '/' ) ) );
//		foreach ( $explode as $part ) {
//			if ( $part === '..' && $path && end( $path ) !== '..' ) {
//				array_pop( $path );
//			}
//			else {
//				if ( $part !== '.' ) {
//					$path[] = $part;
//				}
//			}
//		}
//
//		$path = implode( DIRECTORY_SEPARATOR, $path );
//
//		if ( false === file_exists( $path ) ) {
//
//			$this->log->warning( "EnvironmentAwareTrait: getPath: '$path' not found." );
//			// if ( class_exists( Debug::class ) ) {
//			// 	$message = "EnvironmentAwareTrait: getPath: '$path' not found.";
//			// 	Debug::log( $message, debug_backtrace( \DEBUG_BACKTRACE_IGNORE_ARGS, 2 ), Level::ERROR );
//			// }
//
//			return $path;
//		}
//
//		self::$pathCache[ $key ] = $path;
//
//		return $path;
//	}
}