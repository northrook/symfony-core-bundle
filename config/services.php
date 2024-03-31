<?php

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Northrook\Favicon\FaviconBundle;
use Northrook\Symfony\Core\Components\LatteComponentPreprocessor;
use Northrook\Symfony\Core\Controller\CoreAdminController;
use Northrook\Symfony\Core\Controller\CoreApiController;
use Northrook\Symfony\Core\EventSubscriber\LogAggregationSubscriber;
use Northrook\Symfony\Core\File;
use Northrook\Symfony\Core\Services\ContentManagementService;
use Northrook\Symfony\Core\Services\CurrentRequestService;
use Northrook\Symfony\Core\Services\PathfinderService;
use Northrook\Symfony\Core\Services\StylesheetGenerationService;

return static function ( ContainerConfigurator $container ) : void {
    //
    // Parameters
    $container->parameters()
              ->set( 'dir.root', File::parameterDirname( '%kernel.project_dir%' ) )
              ->set( 'dir.assets', File::parameterDirname( '%kernel.project_dir%/assets' ) )
              ->set( 'dir.public', File::parameterDirname( "%kernel.project_dir%/public" ) )
              ->set( 'dir.public.assets', File::parameterDirname( "%kernel.project_dir%/public/assets" ) )
              ->set( 'dir.cache', File::parameterDirname( "%kernel.project_dir%/var/cache" ) )
              ->set( 'dir.templates', File::parameterDirname( "%kernel.project_dir%/templates" ) )
              ->set( 'dir.latte.templates.core', File::parameterDirname( '../../templates' ) )
              ->set( 'path.favicon', File::parameterDirname( '../../assets/icons/favicon.default.svg' ) )
              ->set( 'ttl.cache', 86400 )
    ;
    //
    // Services
    $container->services()
        //
        //
        // ☕ - Core API Controller
              ->set( 'core.controller.api', CoreApiController::class )
              ->tag( 'controller.service_arguments' )
              ->args(
                  [
                      service( 'core.service.pathfinder' ),
                      service( 'parameter_bag' ),
                      service( 'logger' )->nullOnInvalid(),
                  ],
              )
        //
        //
        // ☕ - Core Admin Controller
              ->set( 'core.controller.admin', CoreAdminController::class )
              ->tag( 'controller.service_arguments' )
              ->args(
                  [
                      service( 'core.service.request' ),
                      service( 'core.service.pathfinder' ),
                      service( 'parameter_bag' ),
                      service( 'core.service.stylesheet' ),
                      service( 'logger' )->nullOnInvalid(),
                  ],
              )
        //
        //
        // ☕ - Favicon Generator
              ->set( FaviconBundle::class )
        //
        //
        // ☕ - Core Latte Preprocessor
              ->set( 'core.latte.preprocessor', LatteComponentPreprocessor::class )
              ->args(
                  [
                      service( 'logger' )->nullOnInvalid(),
                      service( 'debug.stopwatch' )->nullOnInvalid(),
                  ],
              )
              ->alias( LatteComponentPreprocessor::class, 'core.latte.preprocessor' )
        //
        //
        // 🗃️️ - Content Management Service
              ->set( 'core.service.content', ContentManagementService::class )
              ->args(
                  [
                      service( 'logger' )->nullOnInvalid(),
                  ],
              )
              ->autowire()
              ->alias( ContentManagementService::class, 'core.service.content' )
        //
        //
        // 📥 - Current Request Service
              ->set( 'core.service.request', CurrentRequestService::class )
              ->args(
                  [
                      service( 'request_stack' ),
                      service( 'logger' )->nullOnInvalid(),
                  ],
              )
              ->autowire()
              ->public()
              ->alias( CurrentRequestService::class, 'core.service.request' )
        //
        //
        // 🧭 - Pathfinder Service
              ->set( 'core.service.pathfinder', PathfinderService::class )
              ->args(
                  [
                      service( 'parameter_bag' ),
                      service( 'logger' )->nullOnInvalid(),
                  ],
              )
              ->autowire()
              ->public()
              ->alias( PathfinderService::class, 'core.service.pathfinder' )
        //
        //
        // 🧭 - Pathfinder Service
              ->set( 'core.service.stylesheets', StylesheetGenerationService::class )
              ->args(
                  [
                      service( 'core.service.pathfinder' ),
                      service( 'core.service.request' ),
                      service( 'logger' )->nullOnInvalid(),
                      service( 'debug.stopwatch' )->nullOnInvalid(),
                  ],
              )
              ->autowire()
              ->public()
              ->alias( StylesheetGenerationService::class, 'core.service.stylesheets' )
        //
        //
        // 🗂 - Log Aggregating Event Subscriber
              ->set( LogAggregationSubscriber::class )
              ->args(
                  [
                      service( 'logger' )->nullOnInvalid(),
                  ],
              )
              ->tag( 'kernel.event_subscriber', [ 'priority' => 100 ] )
    ;
};