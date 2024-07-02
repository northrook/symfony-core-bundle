<?php

//------------------------------------------------------------------
// config / Cache
//------------------------------------------------------------------

declare( strict_types = 1 );

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Northrook\CacheManager;
use Symfony\Component\Cache\Adapter\PhpFilesAdapter;

return static function ( ContainerConfigurator $container ) : void {

    $cache = $container->services();

    /** # ⚡
     * Cache Manager
     */
    $cache->set( CacheManager::class )
          ->tag( 'controller.service_arguments' )
          ->args(
              [
                  '%kernel.cache_dir%',
                  '%dir.manifest%',
                  [],
                  service( 'logger' )->nullOnInvalid(),
              ],
          )
          ->call(
              method    : 'addPool',
              arguments : [ 'persistentMemoCache', service( 'core.persistentMemoCache' ) ],
          )
          ->autowire();

    // PersistentMemoCache
    $cache->set( 'core.persistentMemoCache', PhpFilesAdapter::class )
          ->args( [ 'core', 0, '%kernel.cache_dir%/persistentMemoCache' ] )
          ->tag( 'cache.pool' );


    // Pathfinder
    $cache->set( 'core.pathfinderCache', PhpFilesAdapter::class )
          ->args( [ 'core.pathfinder', 0, '%kernel.cache_dir%/pathfinderCache' ] )
          ->tag( 'cache.pool' );
};