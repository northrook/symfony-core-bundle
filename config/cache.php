<?php

//------------------------------------------------------------------
// config / Cache
//------------------------------------------------------------------

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Symfony\Component\Cache\Adapter\PhpFilesAdapter;

return static function ( ContainerConfigurator $container ) : void {
    
    $container->services()->set( 'cache.core.pathfinder', PhpFilesAdapter::class )
        ->args([
            'core',
            0,
            '%kernel.project_dir%/var/cache/core/pathfinder',
          ])
        ->tag('cache.pool');
};