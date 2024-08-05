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
        ->autoconfigure()
        ->public();

    /** # ://
     * Current Request Service
     */
    $services->set( CurrentRequest::class )
             ->args( [ service( 'request_stack' ) ] );

    /** # ../
     * Path Service
     *
     * {@see Pathfinder::$directories} will be assigned by the {@see PathfinderServicePass}
     */
    $services->set( Pathfinder::class )
             ->args(
                 [
                     [], // $directoryParameters
                     service( 'core.cache.pathfinder' ),
                     service( 'logger' )->nullOnInvalid(),
                 ],
             );
};