<?php
declare( strict_types = 1 );

use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;

return static function ( RoutingConfigurator $routes ) : void {
    $routes->import(
        [
            'path'      => dirname( __DIR__, 2 ) . '/src/Controller',
            'namespace' => 'App\Controller',
        ],
        'attribute',
    );
};