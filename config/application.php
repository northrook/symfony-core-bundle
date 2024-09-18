<?php

/*-------------------------------------------------------------------/
   config/settings

    Application Environment
    Core Application Settings

/-------------------------------------------------------------------*/

declare( strict_types = 1 );

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Northrook\Env;
use Northrook\Settings;
use Northrook\Symfony\Core\Service\CurrentRequest;


return static function( ContainerConfigurator $container ) : void
{
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