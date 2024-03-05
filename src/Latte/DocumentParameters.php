<?php

namespace Northrook\Symfony\Core\Latte;

use Northrook\Symfony\Core\Services\ContentManagementService;
use Northrook\Symfony\Core\Services\CurrentRequestService;
use Northrook\Symfony\Latte;
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
		private readonly ?LoggerInterface         $logger = null,
	) {}

	private function getTitle() {}

	private function getDescription() {}

	private function getKeywords() {}
}