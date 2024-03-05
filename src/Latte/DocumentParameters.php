<?php

namespace Northrook\Symfony\Core\Latte;

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

	private array $scripts     = [];
	private array $stylesheets = [];
	private array $meta        = [];

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

	public function addStylesheet( string ...$public ) : self {
		foreach ( $public as $path ) {
			$this->stylesheets[] = Path::type( $this->path->get( 'dir.public' ) . $path );
		}
		return $this;
	}

	public function addScript( string ...$public ) : self {
		foreach ( $public as $path ) {
			$this->scripts[] = Path::type( $this->path->get( 'dir.public' ) . $path );
		}
		return $this;
	}

	private function getTitle() {}

	private function getDescription() {}

	private function getKeywords() {}
}