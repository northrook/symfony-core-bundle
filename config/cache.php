<?php

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Symfony\Component\Cache\Adapter\PhpFilesAdapter;

return static function ( ContainerConfigurator $container ) : void {

    $services = $container->services();

    $services->set( 'cache.core.pathfinder', PhpFilesAdapter::class )
        ->args(['core', 0, '%kernel.project_dir%/var/cache/core/pathfinder'])
        ->tag('cache.pool');

};