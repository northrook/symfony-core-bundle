<?php

declare( strict_types = 1 );

use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;

return static function ( RoutingConfigurator $routes ) : void {

    $routes->add( 'core:security:logout', '/logout' )
        // ->controller( [ 'core.controller.api', 'favicon' ] )
           ->methods( [ 'GET' ] )
    ;

    $routes->add( 'core:security:verify-email', '/api/auth/verify-email' )
           ->controller( [ 'core.controller.security', 'verifyEmail' ] )
           ->methods( [ 'GET' ] )
    ;
};