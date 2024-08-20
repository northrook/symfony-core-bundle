<?php

/*-------------------------------------------------------------------/
   config/latte
/-------------------------------------------------------------------*/

declare( strict_types = 1 );

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Northrook\Latte\CacheExtension;
use Northrook\Latte\Extension\ComponentExtension;
use Northrook\Latte\Extension\ElementExtension;
use Northrook\Latte\Extension\FormatterExtension;
use Northrook\Latte\Extension\OptimizerExtension;
use Northrook\Latte\Extension\RenderExtension;

return static function ( ContainerConfigurator $container ) : void {

    $latte = $container->services();

    $latte->defaults()
          ->autowire()
          ->autoconfigure();

    $latte->set( ComponentExtension::class );
    $latte->set( ElementExtension::class );
    $latte->set( RenderExtension::class );
    $latte->set( FormatterExtension::class );
    $latte->set( OptimizerExtension::class );

    $latte->set( CacheExtension::class )
          ->args(
              [
                  service( 'core.latte.cache' )->nullOnInvalid(),
                  service( 'logger' )->nullOnInvalid(),
              ],
          );
};