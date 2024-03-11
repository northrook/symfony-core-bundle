<?php declare( strict_types = 1 );

namespace Northrook\Symfony\Core;

use Northrook\Symfony\Core\DependencyInjection\ContainerInstance;
use Symfony\Component\HttpKernel\Bundle\Bundle;

/**
 * @version 1.0 ☑️
 * @author  Martin Nielsen <mn@northrook.com>
 *
 * @link    https://github.com/northrook Documentation
 * @todo    Update URL to documentation : root of symfony-core-bundle
 */
final class SymfonyCoreBundle extends Bundle
{
    public function boot() {
        parent::boot();
        ContainerInstance::set( $this->container );
    }

    public function getPath() : string {
        return dirname( __DIR__ );
    }

}