<?php

//------------------------------------------------------------------
// config / Controllers
//------------------------------------------------------------------

declare( strict_types = 1 );

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Northrook\Symfony\Core\Autowire\Authentication;
use Northrook\Symfony\Core\Autowire\CurrentRequest;
use Northrook\Symfony\Core\Controller\AdminController;
use Northrook\Symfony\Core\Controller\ApiController;
use Northrook\Symfony\Core\Controller\PublicController;
use Northrook\Symfony\Core\Controller\SecurityController;
use Northrook\Symfony\Service\Document\DocumentService;
use Symfony\Component\HttpKernel\Profiler\Profiler;

return static function ( ContainerConfigurator $container ) : void {

    /**
     * Profiler Alias for `autowiring`
     */
    $container->services()->alias( Profiler::class, 'profiler' );

    $controllers = $container->services()
                             ->defaults()
                             ->autoconfigure();

    /**
     * Core `Public` Controller
     */
    $controllers->set( 'core.controller.public', PublicController::class )
                ->tag( 'controller.service_arguments' )
                ->args(
                    [
                        service( CurrentRequest::class ),
                        service( DocumentService::class ),
                        service( Authentication::class ),
                    ],
                );

    /**
     * Core `Admin` Controller
     */
    $controllers->set( 'core.controller.admin', AdminController::class )
                ->tag( 'controller.service_arguments' )
                ->args(
                    [
                        service( CurrentRequest::class ),
                        service( DocumentService::class ),
                        service( Authentication::class ),
                    ],
                );

    /**
     * Core `Security` Controller
     */
    $controllers->set( 'core.controller.security', SecurityController::class )
                ->tag( 'controller.service_arguments' )
                ->args(
                    [
                        service( CurrentRequest::class ),
                    ],
                );
    /**
     * Core `API` Controller
     */
    $controllers->set( 'core.controller.api', ApiController::class )
                ->tag( 'controller.service_arguments' )
                ->args(
                    [
                        service( CurrentRequest::class ),
                    ],
                );


};