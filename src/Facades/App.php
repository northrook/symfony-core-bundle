<?php

namespace Northrook\Symfony\Core\Facades;

use JetBrains\PhpStorm\ExpectedValues;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\HttpKernel\KernelInterface;
use UnitEnum;

final class App extends AbstractFacade
{

	private static function kernel() : KernelInterface {
		return self::getContainerService( 'kernel' );
	}

	public static function env(
		#[ExpectedValues( [ 'dev', 'prod', 'debug' ] )]
		string $is,
	) : bool {
		return match ( $is ) {
			'dev'   => App::kernel()->getEnvironment() == 'dev',
			'prod'  => App::kernel()->getEnvironment() == 'prod',
			'debug' => App::kernel()->isDebug(),
			default => false,
		};
	}

}