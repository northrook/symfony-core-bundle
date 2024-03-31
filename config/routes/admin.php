<?php

declare( strict_types = 1 );

use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;

return static function ( RoutingConfigurator $routes ) : void {

    $routes->add( 'core:admin', '/{action}' )
           ->controller( [ 'core.controller.admin', 'index' ] )
           ->requirements( [ 'action' => '.+' ] )
           ->defaults( [ 'action' => 'dashboard' ] )
    ;
};