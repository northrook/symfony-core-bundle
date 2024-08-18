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

return static function ( ContainerConfigurator $container ) : void {

    $container->services()
              ->defaults()
              ->public()

        // Env
              ->set( Env::class )
              ->args( [ '%kernel.environment%', '%kernel.debug%' ] )

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
              );
};