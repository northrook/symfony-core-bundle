<?php

/*-------------------------------------------------------------------/
   config/autowire
/-------------------------------------------------------------------*/

declare( strict_types = 1 );

use Northrook\Symfony\Core\Autowire\CurrentRequest;
use Northrook\Symfony\Core\Autowire\Pathfinder;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use function Symfony\Component\DependencyInjection\Loader\Configurator\service;

return static function ( ContainerConfigurator $container ) : void {

    $services = $container->services();

    // Autowire Core dependencies
    $services
        ->defaults()
        ->autowire()
        ->autoconfigure();

    /** # ://
     * Current Request Service
     */
    $services->set( 'core.current_request', CurrentRequest::class )
             ->args( [ service( 'request_stack' ) ] )
             ->public()
             ->alias( CurrentRequest::class, 'core.current_request' );

    /** # ../
     * Path Service
     *
     * {@see Pathfinder::$directories} will be assigned by the {@see PathfinderServicePass}
     */
    $services->set( 'core.pathfinder', Pathfinder::class )
             ->args(
                 [
                     [], // $directoryParameters
                     service( 'core.cache.pathfinder' ),
                     service( 'logger' )->nullOnInvalid(),
                 ],
             )
             ->public()
             ->alias( Pathfinder::class, 'core.pathfinder' );
};