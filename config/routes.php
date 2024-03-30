<?php
declare( strict_types = 1 );

use Northrook\Symfony\Core\Controller\CoreApiController;
use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;

return static function ( RoutingConfigurator $routes ) : void {
    $routes->add( 'api:favicon', 'api/favicon/{action}' )
           ->controller( [ CoreApiController::class, 'favicon' ] )
           ->methods( [ 'GET' ] )
    ;
};