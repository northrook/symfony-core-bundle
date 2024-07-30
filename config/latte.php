<?php

/*-------------------------------------------------------------------/
   config/latte
/-------------------------------------------------------------------*/

declare( strict_types = 1 );

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Northrook\Latte\CacheExtension;
use Northrook\Latte\Compiler\ComponentParser;
use Northrook\Latte\Compiler\Loader;

return static function ( ContainerConfigurator $container ) : void {

    $latte = $container->services();

    $latte->defaults()
          ->autowire()
          ->autoconfigure();

    $latte->set( Loader::class )
          ->args( [ inline_service( ComponentParser::class ) ] );

    $latte->set( 'core.latte_extension.cache', CacheExtension::class )
          ->args(
              [
                  service( 'core.latte.cache' )->nullOnInvalid(),
                  service( 'logger' )->nullOnInvalid(),
              ],
          );
};