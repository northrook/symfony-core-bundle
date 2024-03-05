<?php

namespace Northrook\Symfony\Core\Latte;

use Northrook\Support\Str;
use Northrook\Symfony\Core\Services\ContentManagementService;
use Northrook\Symfony\Core\Services\CurrentRequestService;
use Northrook\Symfony\Core\Services\PathfinderService;
use Northrook\Symfony\Latte;
use Northrook\Types\Path;
use Psr\Log\LoggerInterface;

/**
 * Used by {@see Latte\Environment}.
 *
 * Available in `.latte` templates as {@see $document}.
 *
 * @property string $title
 * @property string $description
 * @property string $keywords
 *
 */
final class DocumentParameters
{

	public array $scripts     = [];
	public array $stylesheets = [];
	public array $meta        = [];

	public function __get( string $name ) {
		$name = "get" . ucfirst( $name );
		if ( method_exists( $this, $name ) ) {
			return $this->$name() ?? null;
		}

		return null;
	}

	public function __construct(
		private readonly CurrentRequestService    $request,
		private readonly ContentManagementService $content,
		private readonly PathfinderService        $path,
		private readonly ?LoggerInterface         $logger = null,
	) {}

	public function setContent( string $content ) : self {
		$this->content->set( $content );
		return $this;
	}


	public function addStylesheet( string ...$styles ) : self {
		foreach ( $styles as $path ) {
			$this->stylesheets[] = Path::type( $this->path->get( 'dir.public' ) . "assets/styles/$path" );
		}
		return $this;
	}

	public function addScript( string ...$scripts ) : self {
		foreach ( $scripts as $path ) {
			$path = Str::end( "assets/scripts/$path", '.js' );
			$this->scripts[] = Path::type( $this->path->get( 'dir.public' ) . $path );
		}
		return $this;
	}

	private function getScripts() : array {
//		$scripts = new st
//		foreach ( $this->scripts as $key => $script ) {
//			$this->scripts[ $key ] = $script;
//		}
		return $this->scripts;
	}

	private function getTitle() {}

	private function getDescription() {}

	private function getKeywords() {}

	private function getRobots() {}
}