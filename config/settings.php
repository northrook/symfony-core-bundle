<?php

/*-------------------------------------------------------------------/
   config/settings
/-------------------------------------------------------------------*/

declare( strict_types = 1 );

namespace Symfony\Component\DependencyInjection\Loader\Configurator;


use Northrook\Settings;

return static function ( ContainerConfigurator $container ) : void {

    $container->services()
              ->set( Settings::class )
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