<?php

/*-------------------------------------------------------------------/
   config/autowire
/-------------------------------------------------------------------*/

declare( strict_types = 1 );

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Northrook\Symfony\Core\Autowire\Authentication;
use Northrook\Symfony\Core\Autowire\CurrentRequest;

return static function ( ContainerConfigurator $container ) : void {

    $services = $container->services();

    // Autowire Core dependencies
    $services
        ->defaults()
        ->autowire()
        ->autoconfigure()
        ->public();

    /** # ://
     * Current Request Service
     */
    $services->set( Authentication::class )
             ->args(
                 [
                     service( 'security.authorization_checker' ),
                     service( 'security.token_storage' ),
                     service( 'security.csrf.token_manager' ),
                 ],
             );

    /** # ://
     * Current Request Service
     */
    $services->set( CurrentRequest::class )
             ->args(
                 [
                     service( 'request_stack' ),
                     service( 'http_kernel' ),
                 ],
             );
};