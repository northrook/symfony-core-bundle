<?php

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Northrook\Symfony\Core\Components\LatteComponentPreprocessor;
use Northrook\Symfony\Core\EventSubscriber\LogAggregationSubscriber;
use Northrook\Symfony\Core\Services\ContentManagementService;
use Northrook\Symfony\Core\Services\CurrentRequestService;
use Northrook\Symfony\Core\Services\PathfinderService;
use Northrook\Symfony\Core\Support\Str;

return static function ( ContainerConfigurator $container ) : void {
    //
    // Parameters
    $container->parameters()
              ->set( 'dir.root', Str::parameterDirname( '%kernel.project_dir%' ) )
              ->set( 'dir.assets', Str::parameterDirname( '%kernel.project_dir%/assets' ) )
              ->set( 'dir.public', Str::parameterDirname( "%kernel.project_dir%/public" ) )
              ->set( 'dir.public.assets', Str::parameterDirname( "%kernel.project_dir%/public/assets" ) )
              ->set( 'dir.cache', Str::parameterDirname( "%kernel.project_dir%/var/cache" ) )
              ->set( 'dir.templates', Str::parameterDirname( "%kernel.project_dir%/templates" ) )
              ->set( 'dir.latte.templates.core', Str::parameterDirname( '../../templates' ) )
              ->set( 'ttl.cache', 86400 )
    ;
    //
    // Services
    $container->services()
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