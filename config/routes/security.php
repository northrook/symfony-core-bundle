<?php

declare( strict_types = 1 );

use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;

return static function ( RoutingConfigurator $routes ) : void {

    $routes->add( 'core:security:login', '/login' )
           ->controller( [ 'core.controller.security', 'login' ] )
           ->schemes( [ 'https' ] )
           ->methods( [ 'GET', 'POST' ] );

    $routes->add( 'core:security:logout', '/logout' )
           ->controller( [ 'core.controller.security', 'logout' ] )
           ->schemes( [ 'https' ] )
           ->methods( [ 'GET' ] );

    $routes->add( 'core:security:register', '/register/{username}/' )
           ->controller( [ 'core.controller.security', 'register' ] )
           ->schemes( [ 'https' ] )
           ->methods( [ 'GET', 'POST' ] );


    $routes->add( 'core:security:verify-email', '/register/verify-email/{token}' )
           ->controller( [ 'core.controller.security', 'verifyEmail' ] )
           ->requirements( [ 'action' => '.+' ] )
           ->defaults( [ 'token' => null ] )
           ->schemes( [ 'https' ] )
           ->methods( [ 'GET' ] );


    $routes->add( 'core:security:reset-password', '/login/reset/{token}' )
           ->controller( [ 'core.controller.security', 'resetPassword' ] )
           ->requirements( [ 'action' => '.+' ] )
           ->defaults( [ 'token' => null ] )
           ->schemes( [ 'https' ] )
           ->methods( [ 'GET', 'POST' ] );
};