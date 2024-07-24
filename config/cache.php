<?php

//------------------------------------------------------------------
// config / Cache
//------------------------------------------------------------------

declare( strict_types = 1 );

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Northrook\Cache\MemoizationCache;
use Symfony\Component\Cache\Adapter\PhpFilesAdapter;

return static function ( ContainerConfigurator $container ) : void {

    $cache = $container->services();

    // Latte Template Cache
    $cache->set( 'core.latte.cache', PhpFilesAdapter::class )
          ->args( [ 'core', 0, '%kernel.cache_dir%/latte/cache' ] )
          ->tag( 'cache.pool' );

    // MemoizationCache
    $cache->set( 'core.cache.memoization', PhpFilesAdapter::class )
          ->args( [ 'core', 0, '%kernel.cache_dir%/memoization' ] )
          ->tag( 'cache.pool' );
    
    // Pathfinder
    $cache->set( 'core.cache.pathfinder', PhpFilesAdapter::class )
          ->args( [ 'core.pathfinder', 0, '%kernel.cache_dir%/pathfinder' ] )
          ->tag( 'cache.pool' );

    /** # ⚡
     * MemoizationCache
     */
    $cache->set( MemoizationCache::class )
          ->args(
              [
                  service( 'core.cache.memoization' )->nullOnInvalid(),
                  service( 'logger' )->nullOnInvalid(),
              ],
          );
};