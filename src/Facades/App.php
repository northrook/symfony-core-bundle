<?php

namespace Northrook\Symfony\Core\Facades;

use JetBrains\PhpStorm\ExpectedValues;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use UnitEnum;

final class App extends AbstractFacade
{

	/**
	 * @param  string  $get  {@see ParameterBagInterface::get}
	 * @return string|int|bool|array|float|UnitEnum|null
	 */
	public static function getParameter(
		string $get,
	) : string | int | bool | array | null | float | UnitEnum {
		return self::parameterBag()->get( $get );
	}

	public static function env(
		#[ExpectedValues( [ 'dev', 'prod', 'debug' ] )]
		string $is,
	) : bool {
		return match ( $is ) {
			'dev'   => self::getParameter( 'kernel.environment' ) == 'dev',
			'prod'  => self::getParameter( 'kernel.environment' ) == 'prod',
			'debug' => self::getParameter( 'kernel.debug' ) === true,
			default => false,
		};
	}

}