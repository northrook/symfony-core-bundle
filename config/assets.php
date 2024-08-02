<?php

/*-------------------------------------------------------------------/
   config/assets
/-------------------------------------------------------------------*/

declare( strict_types = 1 );

use Northrook\AssetGenerator\Asset;
use Northrook\IconManager;
use Northrook\Latte\Runtime\ComponentAssetHandler;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use function Northrook\normalizePath;
use function Symfony\Component\DependencyInjection\Loader\Configurator\param;

return static function ( ContainerConfigurator $container ) : void {

    $container->parameters()
              ->set(
                  'path.public.stylesheet',
                  normalizePath( '%dir.public.assets%/stylesheet.css' ),
              )
              ->set(
                  'path.admin.stylesheet',
                  normalizePath( '%dir.public.assets%/admin/stylesheet.css' ),
              );

    $container->services()

        // northrook/assets
              ->set( Asset::class )
              ->args(
                  [
                      param( 'dir.root' ),
                      param( 'dir.var' ),
                      param( 'dir.public' ),
                      param( 'dir.public.assets' ),
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