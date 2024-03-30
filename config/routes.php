<?php
declare( strict_types = 1 );

use Northrook\Symfony\Core\File;
use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;

return static function ( RoutingConfigurator $routes ) : void {
    $routes->import(
        [
            'path'      => File::parameterDirname( '../../src/Controller' ),
            'namespace' => 'App\Controller',
        ],
        'attribute',
    );
};