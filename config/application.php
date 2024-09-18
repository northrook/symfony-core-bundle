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
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Stopwatch\Stopwatch;


return static function( ContainerConfigurator $container ) : void
{
    $container
            ->services()->set( 'core.service_locator' )
            ->tag( 'container.service_locator' )
            ->args(
                    [
                            [
                                // Core
                                CurrentRequest::class                => service( CurrentRequest::class ),
                                Latte::class                         => service( Latte::class ),
                                ToastService::class                  => service( ToastService::class ),

                                // Security
                                TokenStorageInterface::class         => service( 'security.token_storage' ),
                                AuthorizationCheckerInterface::class => service( 'security.authorization_checker' ),

                                // Dev
                                Stopwatch::class                     => service( 'debug.stopwatch' )->nullOnInvalid(),
                                // SerializerInterface::class => service( 'serializer' ),
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