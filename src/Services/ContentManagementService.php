<?php

namespace Northrook\Symfony\Core\Services;

use Northrook\Support\Attributes\Development;
use Northrook\Support\Attributes\EntryPoint;
use Psr\Log\LoggerInterface;
use Stringable;

/**
 *
 * @property array meta // [ 'name' => 'content' ]
 * @property array scripts // [ 'path' => 'path' ]
 * @property array stylesheets // [ 'path' => 'path' ]
 */
#[Development( 'beta' )]
final class ContentManagementService implements Stringable
{

	private array   $history = [];
	private ?string $content = null;

	#[EntryPoint( 'autowire' )]
	public function __construct(
		private readonly ?LoggerInterface $logger = null,
	) {
		$this->history = [];
	}

	public function set( ?string $content ) : void {
		$key = time() . ':' . strlen( $content );
		$this->history[ $key ] = $content;
		$this->content = $content;
	}

	// todo allow negative numbers to go back in history
	public function get( ?int $i = null ) : ?string {

		if ( null === $i ) {
			return $this->content;
		}

		$index = array_keys( $this->history );
		$key = $index[ $i ];
		return $this->history[ $key ];
	}

	public function __toString() : string {
		return $this->content;
	}
}