<?php

//------------------------------------------------------------------
// config / Pathfinder
//------------------------------------------------------------------

declare( strict_types = 1 );

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Northrook\Symfony\Core\Services\PathfinderService;
use Symfony\Component\Cache\Adapter\PhpFilesAdapter;

return static function ( ContainerConfigurator $container ) : void {

    $services = $container->services();

    /** # ../
     * Path Service
     */
    $services->set( 'core.service.pathfinder', PathfinderService::class )
             ->args(
                 [
                     service( 'parameter_bag' ),
                     service( 'core.cache.pathfinder' ),
                     service( 'logger' )->nullOnInvalid(),
                 ],
             )
             ->autowire()
             ->public()
             ->alias( PathfinderService::class, 'core.service.pathfinder' );

    // Cache
    $services->set( 'core.cache.pathfinder', PhpFilesAdapter::class )
             ->args( [ 'core.pathfinder', 0, '%kernel.cache_dir%/core/pathfinder' ] )
             ->tag( 'cache.pool' );
};