<?php

namespace Northrook\Symfony\Core\Services;

use Northrook\Logger\Debug;
use Northrook\Types\Path;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\Exception\ParameterNotFoundException;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class PathfinderService
{

	/**
	 * @var Path[]
	 */
	private array $parameterCache = [];

	public function __construct(
		private readonly ParameterBagInterface $parameter,
		private readonly ?LoggerInterface      $logger = null,
	) {}

	/**
	 * @param string       $root  {@see ParameterBagInterface::get}
	 * @param string|null  $add
	 *
	 * @return string
	 *
	 */
	public function get(
		string  $root,
		?string $add = null,
	) : string {

		$path = $this->parameter( $root );

		if ( $add ) {
			$path->add( $add );
		}

		if ( !$path->isValid ) {
			$this->logger?->error(
				'File requested with parameter {root}' . ( $add ? ' and path {path}'
					: '' ) . ' does not exist.',
				[
					'root'   => $root,
					'path'   => $path,
					'caller' => Debug::backtrace( 2 ),
				],
			);
			return '';
		}

		return $path;
	}

	/**
	 * @param string  $get  {@see ParameterBagInterface::get}
	 *
	 * @return ?Path
	 */
	private function parameter( string $get ) : ?Path {
		if ( !isset( $this->parameterCache[ $get ] ) ) {
			try {
				$this->parameterCache[ $get ] = Path::type( $this->parameter->get( $get ) );
			}
			catch ( ParameterNotFoundException $exception ) {
				$this->logger?->error(
					'Requested parameter {get} does not exist.',
					[
						'get'          => $get,
						'parameterBag' => $this->parameter,
						'exception'    => $exception,
					],
				);
			}
		}

		return $this->parameterCache[ $get ] ?? null;
	}
}