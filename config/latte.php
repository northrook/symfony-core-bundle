<?php

/*-------------------------------------------------------------------/
   config/latte
/-------------------------------------------------------------------*/

declare( strict_types = 1 );

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Northrook\Latte\LatteBundle;

return static function ( ContainerConfigurator $container ) : void {

    $latte = $container->services();

    $latte->defaults()
          ->autowire()
          ->autoconfigure();
    
    $latte->set( 'core.latte_bundle', LatteBundle::class )
          ->args(
              [
                  '$cacheDirectory'      => '%dir.cache.latte%', // $cacheDirectory : string
                  '$templateDirectories' => [], // $templateDirectories : string[]
                  '$globalVariables'     => [], // $globalVariables : array
                  '$extensions'          => [], // $extensions      : array
                  '$preprocessors'       => [], // $preprocessors   : array
                  '$postprocessors'      => [], // $postprocessors  : array
                  '$stopwatch'           => service( 'debug.stopwatch' )->nullOnInvalid(),
                  '$autoRefresh'         => '%core.config.latte.autoRefresh%',
                  '$cacheTTL'            => '%core.config.latte.cacheTTL%',
              ],
          )->alias( LatteBundle::class, 'core.latte_bundle' );
};