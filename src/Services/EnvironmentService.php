<?php

namespace Northrook\Symfony\Core\Services;

use JetBrains\PhpStorm\Deprecated;
use Northrook\Logger\Log;
use Northrook\Support\Attributes\Development;
use Northrook\Support\Attributes\EntryPoint;
use Symfony\Component\DependencyInjection\Exception\ParameterNotFoundException;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use UnitEnum;

/** Interact with environment variables
 *
 * * Run checks against environment status
 * * Get environment variables
 *
 * @property bool $dev
 * @property bool $prod
 * @property bool $debug
 *
 */
#[Development( 'beta' )]
#[Deprecated]
final class EnvironmentService
{

	public function __get( string $name ) : bool {
		return match ( $name ) {
			'dev'   => $this->parameter->get( 'kernel.environment' ) == 'dev',
			'prod'  => $this->parameter->get( 'kernel.environment' ) == 'prod',
			'debug' => $this->parameter->get( 'kernel.debug' ) === true,
			default => false,
		};
	}

	#[EntryPoint( 'autowire' )]
	public function __construct( private readonly ParameterBagInterface $parameter ) {
		trigger_deprecation(
			'Northrook\\Symfony\\Core',
			'beta',
			'EnvironmentService is deprecated. Use App::env() instead.',
		);
	}

	/** Get environment variable
	 *
	 * @param null|string  $key
	 * @param null|string  $default
	 * @param bool         $logOnFail
	 *
	 * @return string|int|bool|array|float|UnitEnum|null
	 */
	public function get(
		?string $key = null,
		?string $default = null,
		bool    $logOnFail = true,
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
}