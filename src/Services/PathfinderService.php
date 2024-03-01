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
	 * @return string
	 *
	 */
	public function get(
		string $key,
	) : string {

		$path = Str::normalizePath( $this->parameter->get( $key ) );

		if ( !file_exists( $path ) ) {
			$this->logger?->error(
				"File requested with parameter {key} does not exist: {path}",
				[
					'key'  => $key,
					'path' => $path,
				],
			);
			return $key;
		}

		return $path;
	}
}