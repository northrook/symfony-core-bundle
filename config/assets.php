<?php

/*-------------------------------------------------------------------/
   config/assets
/-------------------------------------------------------------------*/

declare( strict_types = 1 );

use Northrook\AssetManager;
use Northrook\IconManager;
use Northrook\Runtime\ComponentAssetHandler;
use Northrook\Symfony\Core\Service\DesignSystemService;
use Northrook\Symfony\Core\Service\StylesheetGenerator;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use function Northrook\normalizePath;
use function Symfony\Component\DependencyInjection\Loader\Configurator\param;
use function Symfony\Component\DependencyInjection\Loader\Configurator\service;

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
              )

        // northrook/components
              ->set( ComponentAssetHandler::class )
              ->args(
                  [ param( 'dir.core.templates' ) . DIRECTORY_SEPARATOR . 'components' ],
              )

        // northrook/icon-manager
              ->set( IconManager::class );
};