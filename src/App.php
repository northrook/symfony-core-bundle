<?php

namespace Northrook\Symfony\Core;

use JetBrains\PhpStorm\ExpectedValues;
use Northrook\Logger\Log;
use Symfony\Component\DependencyInjection\Exception\ParameterNotFoundException;

final class App extends Facades\AbstractFacade
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


	/**
	 * @param  string  $get  {@see ParameterBagInterface::get}
	 * @return string
	 */
	public static function getParameter( string $get ) : string {

		try {
			return App::kernel()->getContainer()->getParameter( $get );
		}
		catch ( ParameterNotFoundException $exception ) {
			Log::Alert(
				'Failed getting parameter {get}, the parameter does not exist. Returned raw string:{get} instead.',
				[
					'get'       => $get,
					'exception' => $exception,
				],
			);
			return $get;
		}
	}



}