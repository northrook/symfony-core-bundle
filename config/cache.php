<?php

//------------------------------------------------------------------
// config / Cache
//------------------------------------------------------------------

declare( strict_types = 1 );

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Northrook\Cache\MemoizationCache;
use Northrook\Symfony\Core\EventSubscriber\DeferredCacheEvent;
use Symfony\Component\Cache\Adapter\PhpArrayAdapter;
use Symfony\Component\Cache\Adapter\PhpFilesAdapter;


return static function( ContainerConfigurator $container ) : void
{
    $cache = $container->services();

    // Asset Cache
    $cache
        ->set( 'core.cache.assets', PhpFilesAdapter::class )
        ->args( [ 'core', 0, '%kernel.cache_dir%/assets' ] )
        ->tag( 'cache.pool' )
    ;

    // Latte Template Cache
    $cache
        ->set( 'core.cache.latte', PhpFilesAdapter::class )
        ->args( [ 'core', 0, '%kernel.cache_dir%/latte/cache' ] )
        ->tag( 'cache.pool' )
    ;

    // MemoizationCache
    $cache
        ->set( 'core.cache.memoization', PhpFilesAdapter::class )
        ->args( [ 'core', 0, '%kernel.cache_dir%/memoization' ] )
        ->tag( 'cache.pool' )
    ;

    // Pathfinder
    $cache
        ->set( 'core.cache.pathfinder', PhpArrayAdapter::class )
        ->args( [ 'core.pathfinder', 0, '%kernel.cache_dir%/pathfinder' ] )
        ->tag( 'cache.pool' )
    ;

    /** # ⚡
     * MemoizationCache
     */
    $cache
        ->set( MemoizationCache::class )
        ->args(
            [
                service( 'core.cache.memoization' )->nullOnInvalid(),
                service( 'logger' )->nullOnInvalid(),
            ],
        )
    ;

    /** # 🗃️
     * Commit deferred cache items.
     */
    $cache
        ->set( DeferredCacheEvent::class )
        ->args(
            [
                service( 'core.cache.latte' ),
                service( 'core.cache.memoization' ),
                service( 'core.cache.pathfinder' ),
            ],
        )
        ->tag( 'kernel.event_subscriber' )
    ;
};