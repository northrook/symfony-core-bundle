<?php

/*-------------------------------------------------------------------/
   config/admin

    Admin Environment

/-------------------------------------------------------------------*/

declare( strict_types = 1 );

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Northrook\Symfony\Core\Admin\SidebarMenu;
use Northrook\Symfony\Core\Service\CurrentRequest;


return static function( ContainerConfigurator $container ) : void
{
    $container
            ->services()
            ->set( SidebarMenu::class )
            ->tag( 'controller.service_arguments' )
            ->args(
                    [
                            service( CurrentRequest::class ),
                    ],
            )
    ;
};