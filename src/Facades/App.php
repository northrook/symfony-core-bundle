<?php

namespace Northrook\Symfony\Core\Facades;

use JetBrains\PhpStorm\ExpectedValues;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use UnitEnum;

final class App extends AbstractFacade
{

	public static function getParameter(
		#[ExpectedValues( valuesFromClass : ParameterBagInterface::class )]
		?string $name = null,
	) : string | int | bool | array | null | float | UnitEnum {
		return self::environment()->get( $name );
	}

	public static function env(
		#[ExpectedValues( [ 'dev', 'prod', 'debug' ] )]
		string $is,
	) : bool {
		return self::environment()->$is;
	}

}