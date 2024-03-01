<?php

namespace Northrook\Symfony\Core\Services;

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
	 * @param  string  $root  {@see ParameterBagInterface::get}
	 * @param  string|null  $path
	 * @return string
	 *
	 */
	public function get(
		string  $root,
		?string $path = null,
	) : string {

		$string = Str::normalizePath( $this->parameter->get( $root ) . "/$path" );

		if ( false === file_exists( $path ) ) {
			$this->logger?->error(
				'File requested with parameter {root}' . ( $path ? ' and path {path}'
					: '' ) . ' does not exist: {string}',
				[
					'root'   => $root,
					'path'   => $path,
					'string' => $string,
					'caller' => debug_backtrace( DEBUG_BACKTRACE_IGNORE_ARGS )[ 0 ],
				],
			);
			return $string;
		}

		return $string;
	}
}