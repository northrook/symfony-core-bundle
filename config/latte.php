<?php

/*-------------------------------------------------------------------/
   config/latte
/-------------------------------------------------------------------*/

declare( strict_types = 1 );

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Northrook\Latte\CacheExtension;

return static function ( ContainerConfigurator $container ) : void {

    $latte = $container->services();

    $latte->defaults()
          ->autowire()
          ->autoconfigure();


    $latte->set( 'core.latte_extension.cache', CacheExtension::class )
          ->args(
              [
                  service( 'core.latte.cache' )->nullOnInvalid(),
                  service( 'logger' )->nullOnInvalid(),
              ],
          );
};