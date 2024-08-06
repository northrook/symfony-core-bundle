<?php

/*-------------------------------------------------------------------/
   config/assets
/-------------------------------------------------------------------*/

declare( strict_types = 1 );

use Northrook\AssetGenerator\Asset;
use Northrook\IconManager;
use Northrook\Latte\Runtime\ComponentAssetHandler;
use Northrook\Symfony\Core\Autowire\Pathfinder;
use Northrook\Symfony\Core\Service\DesignSystemService;
use Northrook\Symfony\Core\Service\StylesheetGenerator;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use function Northrook\normalizePath;
use function Symfony\Component\DependencyInjection\Loader\Configurator\param;
use function Symfony\Component\DependencyInjection\Loader\Configurator\service;

return static function ( ContainerConfigurator $container ) : void {

    $container->parameters()
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

        // service/stylesheetGenerator
              ->set( StylesheetGenerator::class )
              ->tag( 'controller.service_arguments' )
              ->args(
                  [
                      service( Pathfinder::class ),
                      service( DesignSystemService::class ),
                      service( 'logger' )->nullOnInvalid(),
                  ],
              )

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