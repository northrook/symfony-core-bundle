<?php

namespace Northrook\Symfony\Core\Facades;

use JetBrains\PhpStorm\ExpectedValues;
use Northrook\Logger\Log;

final class App extends AbstractFacade
{

	public static function env(
		#[ExpectedValues( [ 'dev', 'prod', 'debug' ] )]
		string $is,
	) : bool {
		if ( self::kernel() === null ) {
			Log::Alert(
				'Failed checking if {call} is {is}, as {kernel} is {status}. Returned {return} instead.',
				[
					'is'     => $is,
					'call'   => 'App::env',
					'kernel' => 'App::kernel',
					'status' => 'null',
					'return' => 'false',
				],
			);
			return false;
		}
		return match ( $is ) {
			'dev'   => App::kernel()->getEnvironment() == 'dev',
			'prod'  => App::kernel()->getEnvironment() == 'prod',
			'debug' => App::kernel()->isDebug(),
			default => false,
		};
	}

}