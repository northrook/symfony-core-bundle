<?php

/*-------------------------------------------------------------------/
   config/assets
/-------------------------------------------------------------------*/

declare( strict_types = 1 );

use Northrook\AssetGenerator\Asset;
use Northrook\IconManager;
use Northrook\Latte\Runtime\ComponentAssetHandler;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use function Symfony\Component\DependencyInjection\Loader\Configurator\param;

return static function ( ContainerConfigurator $container ) : void {

    $services = $container->services();

    $services->set( Asset::class )
             ->args(
                 [
                     param( 'dir.root' ),
                     param( 'dir.var' ),
                     param( 'dir.public' ),
                     param( 'dir.public.assets' ),
                 ],
             );

    $services->set( ComponentAssetHandler::class )
             ->args( [ param( 'dir.core.templates' ) . DIRECTORY_SEPARATOR . 'components' ] );

    $services->set( IconManager::class );
};