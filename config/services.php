<?php

//------------------------------------------------------------------
// config / Services
//------------------------------------------------------------------

declare( strict_types = 1 );

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Northrook\Favicon\FaviconBundle;
use Northrook\Support\File;
use Northrook\Symfony\Components\LatteComponentPreprocessor;
use Northrook\Symfony\Core\DependencyInjection\CoreDependencies;
use Northrook\Symfony\Core\EventSubscriber\LogAggregationSubscriber;
use Northrook\Symfony\Core\Services\CurrentRequestService;
use Northrook\Symfony\Core\Services\DocumentService;
use Northrook\Symfony\Core\Services\FormService;
use Northrook\Symfony\Core\Services\MailerService;
use Northrook\Symfony\Core\Services\NotificationService;
use Northrook\Symfony\Core\Services\PathfinderService;
use Northrook\Symfony\Core\Services\SettingsManagementService;
use Northrook\Symfony\Core\Services\StylesheetGenerationService;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Stopwatch\Stopwatch;

return static function ( ContainerConfigurator $container ) : void {

    $services   = $container->services();
    $parameters = $container->parameters();


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

    //--------------------------------------------------------------------
    // Core Path Helper
    //--------------------------------------------------------------------


    /** # ☕
     * Latte Preprocessor
     */
    $services->set( 'core.latte.preprocessor', LatteComponentPreprocessor::class )
             ->args( [ service( 'core.dependencies' ) ] );

    /** # ⭐
     * Favicon Generator
     */
    $services->set( FaviconBundle::class );


    //--------------------------------------------------------------------
    // Dependencies
    //--------------------------------------------------------------------

    /**
     * Core Controller Dependencies.
     *
     * Inject into `__construct()` as `protected readonly CoreDependencies $get`.
     *
     * - {@see RouterInterface}
     * - {@see HttpKernelInterface}
     * - {@see SerializerInterface}
     * - {@see AuthorizationCheckerInterface}
     * - {@see LoggerInterface} - optional
     * - {@see Stopwatch} - optional
     *
     */
    $services->set( 'core.dependencies', CoreDependencies::class )
             ->args(
                 [
                     service_closure( 'router' ),
                     service_closure( 'http_kernel' ),
                     service_closure( 'parameter_bag' ),
                     service_closure( 'core.service.request' ),
                     service_closure( 'serializer' ),
                     service_closure( 'security.authorization_checker' ),
                     service_closure( 'security.token_storage' ),
                     service_closure( 'security.csrf.token_manager' ),
                     service_closure( 'latte.environment' ),
                     service_closure( 'core.service.document' ),
                     service_closure( 'core.service.stylesheet' ),
                     service_closure( 'core.service.mailer' ),
                     service_closure( 'logger' )->nullOnInvalid(),
                     service_closure( 'debug.stopwatch' )->nullOnInvalid(),
                 ],
             );

    /**
     * Settings
     */
    $services->set( 'core.service.settings', SettingsManagementService::class );

    //--------------------------------------------------------------------
    // Event Listeners and Subscribers
    //--------------------------------------------------------------------

    /** # 🗂
     * Log Aggregating Event Subscriber
     */
    $services->set( LogAggregationSubscriber::class )
             ->args( [ service( 'logger' )->nullOnInvalid() ], )
             ->tag( 'kernel.event_subscriber', [ 'priority' => 100 ] );
    
    //--------------------------------------------------------------------
    // Services
    //--------------------------------------------------------------------

    /** # 📧
     * Mailer Service
     */
    $services->set( 'core.service.mailer', MailerService::class )
             ->tag( 'controller.service_arguments' )
             ->args(
                 [
                     service( 'core.service.settings' ),
                     service( 'twig' ),
                     service( 'latte.environment' ),
                 ],
             )
             ->alias( MailerService::class, 'core.service.mailer' );

    /** # 📄
     * Document Service
     */
    $services->set( 'core.service.document', DocumentService::class )
             ->args( [ service( 'core.service.request' ) ] )
             ->autowire()
             ->alias( DocumentService::class, 'core.service.document' );

    /** # 📥
     * Current Request Service
     */
    $services->set( 'core.service.request', CurrentRequestService::class )
             ->args(
                 [
                     service( 'request_stack' ),
                     service( 'logger' )->nullOnInvalid(),
                 ],
             )
             ->autowire()
             ->public()
             ->alias( CurrentRequestService::class, 'core.service.request' );

    /** # ../
     * Path Service
     */
    $services->set( 'core.service.pathfinder', PathfinderService::class )
             ->args(
                 [
                     service( 'parameter_bag' ),
                     service( 'cache.core.pathfinder' ),
                     service( 'logger' )->nullOnInvalid(),
                 ],
             )
             ->autowire()
             ->public()
             ->alias( PathfinderService::class, 'core.service.pathfinder' );
    /** # {}
     * Pathfinder Service
     */
    $services->set( 'core.service.stylesheet', StylesheetGenerationService::class )
             ->tag( 'controller.service_arguments' )
             ->args(
                 [
                     service( 'core.service.request' ),
                     service( 'core.service.pathfinder' ),
                     service( 'logger' )->nullOnInvalid(),
                     service( 'debug.stopwatch' )->nullOnInvalid(),
                 ],
             )
             ->autowire()
             ->public()
             ->alias( StylesheetGenerationService::class, 'core.service.stylesheet' );
    /** # 📩
     * Form Service
     */
    $services->set( 'core.service.form', FormService::class )
             ->tag( 'controller.service_arguments' )
             ->args(
                 [
                     service( 'parameter_bag' ),
                     service( 'security.csrf.token_manager' ),
                 ],
             )
             ->autowire()
             ->public()
             ->alias( FormService::class, 'core.service.form' );


    /** # 📩
     * Form Service
     */
    $services->set( 'core.service.notification', NotificationService::class )
             ->tag( 'controller.service_arguments' )
             ->args(
                 [
                     service( 'core.service.request' ),
                     service( 'parameter_bag' ),
                 ],
             )
             ->autowire()
             ->alias( NotificationService::class, 'core.service.notification' );

};