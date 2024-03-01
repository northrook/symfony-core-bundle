<?php

namespace Northrook\Symfony\Core\Services;

use Exception;
use Northrook\Support\Str;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class PathfinderService
{

	public function __construct(
		private readonly ParameterBagInterface $parameter,
		private readonly ?LoggerInterface      $logger = null,
	) {}

	/**
	 * @param  string  $key  {@see ParameterBagInterface::get}
	 * @param  bool  $strict
	 * @return string
	 *
	 * @throws Exception
	 */
	public function get(
		string $key,
		bool   $strict = false,
	) : string {

		$path = Str::normalizePath( $this->parameter->get( $key ) );

		if ( !file_exists( $path ) ) {
			$this->logger?->error( "File does not exist: $path" );
			if ( $strict ) {
				throw new Exception( "File does not exist: $path" );
			}
		}

		return $path;
	}
}