<?php

//------------------------------------------------------------------
// config / Services
//------------------------------------------------------------------

declare( strict_types = 1 );

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Northrook\Favicon\FaviconBundle;
use Northrook\Symfony\Core\Services\FormService;
use Northrook\Symfony\Core\Services\MailerService;

return static function ( ContainerConfigurator $container ) : void {

    return;

    $services = $container->services();

    $services->defaults()->autowire();

    /** # ⭐
     * Favicon Generator
     */
    $services->set( FaviconBundle::class );


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
};