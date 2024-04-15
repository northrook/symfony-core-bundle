<?php

declare( strict_types = 1 );

use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;

return static function ( RoutingConfigurator $routes ) : void {

    $routes->add( 'core:public', '/{route}' )
           ->controller( [ 'core.controller.public', 'index' ] )
           ->requirements( [ 'route' => '.+' ] )
           ->defaults( [ 'route' => null ] )
           ->schemes( [ 'https' ] )
           ->methods( [ 'GET', 'POST' ] );
};