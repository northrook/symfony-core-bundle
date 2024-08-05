<?php

//------------------------------------------------------------------
// config / Services
//------------------------------------------------------------------

declare( strict_types = 1 );

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Northrook\Favicon\FaviconBundle;
use Northrook\Symfony\Components\LatteComponentPreprocessor;
use Northrook\Symfony\Core\Autowire\Pathfinder;
use Northrook\Symfony\Core\DependencyInjection\CoreDependencies;
use Northrook\Symfony\Core\Services\DocumentService;
use Northrook\Symfony\Core\Services\FormService;
use Northrook\Symfony\Core\Services\MailerService;
use Northrook\Symfony\Core\Services\NotificationService;
use Northrook\Symfony\Core\Services\SettingsManagementService;
use Northrook\Symfony\Core\Services\StylesheetGenerationService;

return static function ( ContainerConfigurator $container ) : void {

    return;

    $services = $container->services();

    $services->defaults()->autowire();


    /** # ☕
     * Latte Preprocessor
     */
    $services->set( 'core.latte.preprocessor', LatteComponentPreprocessor::class )
             ->args( [ service( 'core.dependencies' ) ] );

    /** # ⭐
     * Favicon Generator
     */
    $services->set( FaviconBundle::class );


    /**
     * Settings
     */
    $services->set( 'core.service.settings', SettingsManagementService::class );

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

    /** # {}
     * Stylesheet Service
     */
    $services->set( 'core.service.stylesheet', StylesheetGenerationService::class )
             ->tag( 'controller.service_arguments' )
             ->args(
                 [
                     service( 'core.service.request' ),
                     service( Pathfinder::class ),
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