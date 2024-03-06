<?php

namespace Northrook\Symfony\Core\Facades;

use JetBrains\PhpStorm\ExpectedValues;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use UnitEnum;

final class App extends AbstractFacade
{

	private static function kernel() : HttpKernelInterface {
		return self::getContainerService( 'kernel' );
	}

	public static function env(
		#[ExpectedValues( [ 'dev', 'prod', 'debug' ] )]
		string $is,
	) : bool {
		return false;
//		return match ( $is ) {
//			'dev'   => self::getParameter( 'kernel.environment' ) == 'dev',
//			'prod'  => self::getParameter( 'kernel.environment' ) == 'prod',
//			'debug' => self::getParameter( 'kernel.debug' ) === true,
//			default => false,
//		};
	}

}