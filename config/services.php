<?php

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Northrook\Favicon\FaviconBundle;
use Northrook\Symfony\Core\Controller\AdminController;
use Northrook\Symfony\Core\Controller\ApiController;
use Northrook\Symfony\Core\Controller\PublicController;
use Northrook\Symfony\Core\Controller\SecurityController;
use Northrook\Symfony\Core\EventListener\ExceptionListener;
use Northrook\Symfony\Core\EventSubscriber\LogAggregationSubscriber;
use Northrook\Symfony\Core\File;
use Northrook\Symfony\Core\Latte\LatteComponentPreprocessor;
use Northrook\Symfony\Core\Services\ContentManagementService;
use Northrook\Symfony\Core\Services\CurrentRequestService;
use Northrook\Symfony\Core\Services\FormService;
use Northrook\Symfony\Core\Services\HttpService;
use Northrook\Symfony\Core\Services\MailerService;
use Northrook\Symfony\Core\Services\PathfinderService;
use Northrook\Symfony\Core\Services\SecurityService;
use Northrook\Symfony\Core\Services\SettingsManagementService;
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
              ->set( 'dir.core.templates', File::parameterDirname( '../../templates' ) )
              ->set( 'path.favicon', File::parameterDirname( '../../assets/icons/favicon.default.svg' ) )
              ->set( 'dir.core.assets', File::parameterDirname( '../../assets/' ) )
              ->set( 'ttl.cache', 86400 );
    //
    // Services
    $container->services()
        //
        //
        // ☕ - Core API Controller
              ->set( 'core.controller.api', ApiController::class )
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
        // ☕ - Core Public Controller
              ->set( 'core.controller.public', PublicController::class )
              ->tag( 'controller.service_arguments' )
              ->args(
                  [
                      service( 'router' ),
                      service( 'http_kernel' ),
                      service( 'serializer' )->nullOnInvalid(),
                      service( 'core.service.security' ),
                      service( 'core.service.request' ),
                      service( 'core.service.pathfinder' ),
                      service( 'parameter_bag' ),
                      service( 'core.service.stylesheets' ),
                      service( 'latte.environment' ),
                      service( 'latte.parameters.document' ),
                      service( 'logger' )->nullOnInvalid(),
                      service( 'debug.stopwatch' )->nullOnInvalid(),
                  ],
              )
              ->alias( PublicController::class, 'core.controller.public' )
        //
        //
        // ☕ - Core Admin Controller
              ->set( 'core.controller.admin', AdminController::class )
              ->tag( 'controller.service_arguments' )
              ->args(
                  [
                      service( 'router' ),
                      service( 'http_kernel' ),
                      service( 'serializer' )->nullOnInvalid(),
                      service( 'core.service.security' ),
                      service( 'core.service.request' ),
                      service( 'core.service.pathfinder' ),
                      service( 'parameter_bag' ),
                      service( 'core.service.stylesheets' ),
                      service( 'latte.environment' ),
                      service( 'latte.parameters.document' ),
                      service( 'logger' )->nullOnInvalid(),
                      service( 'debug.stopwatch' )->nullOnInvalid(),
                  ],
              )
              ->alias( AdminController::class, 'core.controller.admin' )
        //
        //
        // 🛡️ - Security Controller
              ->set( 'core.controller.security', SecurityController::class )
              ->tag( 'controller.service_arguments' )
              ->args(
                  [
                      service( 'core.service.security' ),
                      service( 'core.service.request' ),
                      service( 'core.service.settings' ),
                      service( 'latte.environment' ),
                      service( 'latte.parameters.document' ),
                      service( 'logger' )->nullOnInvalid(),
                  ],
              )
              ->alias( SecurityController::class, 'core.controller.security' )
        //
        // ☕ - Latte Preprocessor
              ->set( 'core.latte.preprocessor', LatteComponentPreprocessor::class )
        //
        //  ✨- Favicon Generator
              ->set( FaviconBundle::class )
        //
        //
        // 📧 - Mailer Service
              ->set( 'core.service.mailer', MailerService::class )
              ->tag( 'controller.service_arguments' )
              ->args(
                  [
                      service( 'core.service.settings' ),
                      service( 'twig' ),
                      service( 'latte.environment' ),
                  ],
              )
              ->alias( MailerService::class, 'core.service.mailer' )
        //
        //
        // 🗃️️ - Content Management Service
              ->set( 'core.service.settings', SettingsManagementService::class )
        //
        //
        // 🗃️️ - Content Management Service
              ->set( 'core.service.router', HttpService::class )
              ->args(
                  [
                      service( 'router' ),
                      service( 'http_kernel' ),
                  ],
              )
        //
        //
        // 🛡️️ - Security Service
              ->set( 'core.service.security', SecurityService::class )
              ->args(
                  [
                      service( 'security.authorization_checker' ),
                      service( 'security.token_storage' ),
                      service( 'security.csrf.token_manager' ),
                  ],
              )
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
              ->tag( 'controller.service_arguments' )
              ->args(
                  [
                      service( 'core.service.pathfinder' ),
                      service( 'logger' )->nullOnInvalid(),
                      service( 'debug.stopwatch' )->nullOnInvalid(),
                  ],
              )
              ->autowire()
              ->public()
              ->alias( StylesheetGenerationService::class, 'core.service.stylesheets' )
        //
        //
        // 📩 - Form Service
              ->set( 'core.service.form', FormService::class )
              ->tag( 'controller.service_arguments' )
              ->args(
                  [
                      service( 'parameter_bag' ),
                      service( 'security.csrf.token_manager' ),
                  ],
              )
              ->autowire()
              ->public()
              ->alias( FormService::class, 'core.service.form' )
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
              ->set( ExceptionListener::class )
              ->tag( 'kernel.event_listener', [ 'priority' => 100 ] );
    //
    // end
    ;

};