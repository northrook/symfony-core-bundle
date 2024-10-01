<?php

/*-------------------------------------------------------------------/
   config/assets
/-------------------------------------------------------------------*/

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Northrook\Symfony\Core\Service\{DesignSystemService, StylesheetGenerator};
use Support\Normalize;

// use Northrook\UI\AssetHandler;

return static function( ContainerConfigurator $container ) : void {
    $container->parameters()
        ->set(
            'dir.asset.storage',
            Normalize::path( '%dir.var%/assets' ),
        )
        ->set(
            'path.public.stylesheet',
            Normalize::path( '%dir.assets%/stylesheet.css' ),
        )
        ->set(
            'path.admin.stylesheet',
            Normalize::path( '%dir.assets%/admin.css' ),
        );

    $container->services()

            // service/designSystem
        ->set( DesignSystemService::class )
        ->tag( 'controller.service_arguments' )
        ->args( [service( 'logger' )->nullOnInvalid()] )
        /** # {}
         * Stylesheet Service.
         *
         * `service/stylesheetGenerator`
         */
        ->set( StylesheetGenerator::class )
        ->tag( 'controller.service_arguments' )
        ->args(
            [
                service( DesignSystemService::class ),
                service( 'logger' )->nullOnInvalid(),
            ],
        );

    // northrook/assets
    //       ->set( AssetManager::class )
    //       ->args(
    //           [
    //               param( 'dir.root' ),
    //               param( 'dir.asset.storage' ),
    //               param( 'dir.public' ),
    //               param( 'dir.public.assets' ),
    //               service( 'core.cache.assets' ),
    //           ],
    //       );

    // northrook/components
    //       ->set( AssetHandler::class )
    //       ->args(
    //           [ param( 'dir.public.assets' ), ],
    //       );

    // northrook/icon-manager
    //       ->set( IconManager::class );
};
