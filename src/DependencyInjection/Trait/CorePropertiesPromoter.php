<?php

namespace Northrook\Symfony\Core\DependencyInjection\Trait;

use JetBrains\PhpStorm\Deprecated;
use Northrook\Symfony\Core\DependencyInjection\CoreDependencies;

/**
 * @mixin CoreDependencies
 */
#[Deprecated]
trait CorePropertiesPromoter
{

    protected readonly CoreDependencies $get;

    /**
     * @param string  $service  The property name to retrieve.
     *
     * @return ?object
     */
    final public function __get( string $service ) : ?object {
        return $this->get->getMappedService( $service );
    }

    /** {@see CorePropertiesPromoter} does not allow setting of properties. */
    final public function __set( string $name, $service ) : void {}

    /**
     * Check if a service is present in the {@see serviceMap}.
     *
     * @param string  $service
     *
     * @return bool
     */
    final public function __isset( string $service ) : bool {
        return $this->get->has( $service );
    }
}