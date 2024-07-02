<?php

//------------------------------------------------------------------
// config / Pathfinder
//------------------------------------------------------------------

declare( strict_types = 1 );

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Northrook\Symfony\Core\Services\PathfinderService;

return static function ( ContainerConfigurator $container ) : void {

    $services = $container->services();

    /** # ../
     * Path Service
     */
    $services->set( 'core.service.pathfinder', PathfinderService::class )
             ->args(
                 [
                     service( 'parameter_bag' ),
                     service( 'core.pathfinderCache' ),
                     service( 'logger' )->nullOnInvalid(),
                 ],
             )
             ->autowire()
             ->public()
             ->alias( PathfinderService::class, 'core.service.pathfinder' );
};