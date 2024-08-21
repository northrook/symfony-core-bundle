<?php

/*-------------------------------------------------------------------/
   config/assets
/-------------------------------------------------------------------*/

declare( strict_types = 1 );

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Northrook\AssetManager;
use Northrook\Symfony\Core\Service\DesignSystemService;
use Northrook\Symfony\Core\Service\StylesheetGenerator;
use function Northrook\normalizePath;

// use Northrook\UI\AssetHandler;

return static function ( ContainerConfigurator $container ) : void {

    $container->parameters()
              ->set(
                  'dir.asset.storage',
                  normalizePath( '%dir.var%/assets' ),
              )
              ->set(
                  'path.public.stylesheet',
                  normalizePath( '%dir.assets%/stylesheet.css' ),
              )
              ->set(
                  'path.admin.stylesheet',
                  normalizePath( '%dir.assets%/admin.css' ),
              );

    $container->services()

        // service/designSystem
              ->set( DesignSystemService::class )
              ->tag( 'controller.service_arguments' )
              ->args(
                  [
                      service( 'logger' )->nullOnInvalid(),
                  ],
              )
        /** # {}
         * Stylesheet Service
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
              )

        // northrook/assets
              ->set( AssetManager::class )
              ->args(
                  [
                      param( 'dir.root' ),
                      param( 'dir.asset.storage' ),
                      param( 'dir.public' ),
                      param( 'dir.public.assets' ),
                      service( 'core.cache.assets' ),
                  ],
              );

    // northrook/components
    //       ->set( AssetHandler::class )
    //       ->args(
    //           [ param( 'dir.public.assets' ), ],
    //       );

    // northrook/icon-manager
    //       ->set( IconManager::class );
};