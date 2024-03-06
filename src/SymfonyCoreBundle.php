<?php declare( strict_types = 1 );

namespace Northrook\Symfony\Core;

use Northrook\Symfony\Core\DependencyInjection\FacadesContainerInstance;
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
	public function boot() {
		parent::boot();

		FacadesContainerInstance::setContainer( $this->container );
	}

	public function getPath() : string {
		FacadesContainerInstance::setContainer( $this->container );
		return dirname( __DIR__ );
	}

}