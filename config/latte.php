<?php

/*-------------------------------------------------------------------/
   config/latte
/-------------------------------------------------------------------*/

declare( strict_types = 1 );

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Northrook\Latte\CacheExtension;
use Northrook\Latte\LatteBundle;

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

    $latte->set( 'core.latte_bundle', LatteBundle::class )
          ->args(
              [
                  '$cacheDirectory'      => '%dir.cache.latte%', // $cacheDirectory : string
                  '$templateDirectories' => [], // $templateDirectories : string[]
                  '$globalVariables'     => [], // $globalVariables : array
                  '$extensions'          => [],
                  '$preprocessors'       => [], // $preprocessors   : array
                  '$postprocessors'      => [], // $postprocessors  : array
                  '$stopwatch'           => service( 'debug.stopwatch' )->nullOnInvalid(),
                  '$autoRefresh'         => '%core.config.latte.autoRefresh%',
                  '$cacheTTL'            => '%core.config.latte.cacheTTL%',
              ],
          )
          ->call(
              'addExtension', [
              service( 'core.latte_extension.cache' ),
          ],
          )
          ->alias( LatteBundle::class, 'core.latte_bundle' );
};