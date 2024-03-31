<?php

declare( strict_types = 1 );

use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;

return static function ( RoutingConfigurator $routes ) : void {

    $routes->add( 'core:api:favicon', '/favicon/{action}' )
           ->controller( [ 'core.controller.api', 'favicon' ] )
           ->requirements( [ 'action' => 'generate|purge' ] )
           ->defaults( [ 'action' => 'generate' ] )
           ->methods( [ 'GET' ] )
    ;
};