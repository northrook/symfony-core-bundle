<?php

/*-------------------------------------------------------------------/
   config/settings

    Application Environment
    Core Application Settings

/-------------------------------------------------------------------*/

declare( strict_types = 1 );

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Northrook\Env;
use Northrook\Latte;
use Northrook\Settings;
use Northrook\Symfony\Core\Service\CurrentRequest;
use Northrook\Symfony\Service\Toasts\ToastService;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Stopwatch\Stopwatch;


return static function( ContainerConfigurator $container ) : void
{
    $container
            ->services()->set( 'app.service_locator' )
            ->tag( 'container.service_locator' )
            ->args(
                    [
                            [
                                    RouterInterface::class     => service( 'router' ),
                                    HttpKernelInterface::class => service( 'http_kernel' ),
                                    LoggerInterface::class     => service( 'logger' )->nullOnInvalid(),
                                    Stopwatch::class           => service( 'debug.stopwatch' )->nullOnInvalid(),
                                    // CsrfTokenManagerInterface::class     => service( 'security.csrf.token_manager' ),
                                    // NotificationService::class => service( 'core.service.notification' ),

                                    // Core
                                    CurrentRequest::class      => service( CurrentRequest::class ),
                                    Latte::class               => service( Latte::class ),
                                    ToastService::class        => service( ToastService::class ),

                                    // Dev
                                    SerializerInterface::class => service( 'serializer' ),
                                    // AuthorizationCheckerInterface::class => service( 'security.authorization_checker' ),
                                    // TokenStorageInterface::class         => service( 'security.token_storage' ),
                            ],
                    ],
            )
            ->public()
    ;;

    $container
            ->services()
            ->defaults()
            ->public()

            // Env
            ->set( Env::class )
            ->args( [ '%kernel.environment%', '%kernel.debug%' ] )

            // Current Request Service
            ->set( CurrentRequest::class )
            ->args(
                    [
                            service( 'request_stack' ),
                            service( 'http_kernel' ),
                    ],
            )

            // Settings
            ->set( Settings::class )
            ->autowire()
            ->args(
                    [
                            [],
                            false,
                            null,
                            '%kernel.environment%' !== 'prod',
                            service( 'logger' )->nullOnInvalid(),
                    ],
            )
    ;
};