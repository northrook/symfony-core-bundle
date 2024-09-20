<?php

/*-------------------------------------------------------------------/

 config\Telemetry

/-------------------------------------------------------------------*/

declare( strict_types = 1 );

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Northrook\Symfony\Core\Telemetry;
use Symfony\Component\Stopwatch\Stopwatch;


return static function( ContainerConfigurator $container ) : void
{
    $container->services()
              ->set( Telemetry\Clerk::class )
              ->args( [ service( Stopwatch::class ) ] )

            // EventListener
              ->set( Telemetry\EventListener::class )
              ->tag( 'kernel.event_subscriber' );

    // $container
    //         ->services()
    //         ->set( SidebarMenu::class )
    //         ->tag( 'controller.service_arguments' )
    //         ->args(
    //                 [
    //                         service( CurrentRequest::class ),
    //                 ],
    //         )
    // ;
};