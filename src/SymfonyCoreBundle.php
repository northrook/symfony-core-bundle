<?php declare( strict_types = 1 );

namespace Northrook\Symfony\Core;

use Symfony\Component\HttpKernel\Bundle\Bundle;

/**
 * @version 1.0 ☑️
 * @author Martin Nielsen <mn@northrook.com>
 *
 * @link https://github.com/northrook Documentation
 * @todo Update URL to documentation : root of symfony-core-bundle
 */
final class SymfonyCoreBundle extends Bundle
{
	public function getPath() : string {
		dump( $this );
		return dirname( __DIR__ );
	}
}