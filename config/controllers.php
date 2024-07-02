<?php

//------------------------------------------------------------------
// config / Controllers
//------------------------------------------------------------------

declare( strict_types = 1 );

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Northrook\Symfony\Core\Controller\AdminController;
use Northrook\Symfony\Core\Controller\ApiController;
use Northrook\Symfony\Core\Controller\PublicController;
use Northrook\Symfony\Core\Controller\SecurityController;
use Symfony\Component\HttpKernel\Profiler\Profiler;

return static function ( ContainerConfigurator $container ) : void {

    $controllers = $container->services();

    /**
     * `Error Page` Exception Listener
     */

    // $controllers->set( ExceptionListener::class )
    //             ->tag( 'kernel.event_listener', [ 'priority' => 100 ] )
    //             ->args(
    //                 [
    //                     service( 'core.service.request' ),
    //                     service( 'core.service.document' ),
    //                     service( 'core.service.stylesheet' ),
    //                 ],
    //             );

    return;

    /**
     * Profiler Alias for `autowiring`
     */
    $container->services()->alias( Profiler::class, 'profiler' );

    /**
     * Core `Public` Controller
     */
    $controllers->set( 'core.controller.public', PublicController::class )
                ->tag( 'controller.service_arguments' )
                ->args(
                    [
                        service( 'core.service.request' ),
                        service( 'core.service.document' ),
                        service( 'core.service.stylesheet' ),
                    ],
                );

    /**
     * Core `Admin` Controller
     */
    $controllers->set( 'core.controller.admin', AdminController::class )
                ->tag( 'controller.service_arguments' )
                ->args(
                    [
                        service( 'core.service.request' ),
                        service( 'core.service.document' ),
                        service( 'core.service.stylesheet' ),
                    ],
                );

    /**
     * Core `Security` Controller
     */
    $controllers->set( 'core.controller.security', SecurityController::class )
                ->tag( 'controller.service_arguments' )
                ->args(
                    [
                        service( 'core.service.request' ),
                        service( 'core.service.document' ),
                    ],
                );
    /**
     * Core `API` Controller
     */
    $controllers->set( 'core.controller.api', ApiController::class )
                ->tag( 'controller.service_arguments' )
                ->args(
                    [
                        service( 'core.service.request' ),
                    ],
                );


};