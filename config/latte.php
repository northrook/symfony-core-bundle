<?php

/*-------------------------------------------------------------------/
   config/latte
/-------------------------------------------------------------------*/

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Northrook\Latte\Extension\{CacheExtension, FormatterExtension, OptimizerExtension};
use Northrook\UI\Compiler\Latte\UiCompileExtension;
use Support\Normalize;

return static function( ContainerConfigurator $container ) : void {
    $container
        ->parameters()
        ->set(
            'dir.ui.assets',
            Normalize::path( '%dir.root%/vendor/northrook/ui/assets' ),
        )
        ->set(
            'path.public.stylesheet',
            Normalize::path( '%dir.assets%/stylesheet.css' ),
        )
        ->set(
            'path.admin.stylesheet',
            Normalize::path( '%dir.assets%/admin.css' ),
        );

    $latte = $container->services();

    $latte
        ->defaults()
        ->autowire()
        ->autoconfigure();

    $latte
        ->set( UiCompileExtension::class )
        ->args(
            [
                service( 'core.latte.cache' )->nullOnInvalid(),
            ],
        );
    $latte->set( FormatterExtension::class );
    $latte->set( OptimizerExtension::class );

    $latte
        ->set( CacheExtension::class )
        ->args(
            [
                service( 'core.latte.cache' )->nullOnInvalid(),
                service( 'logger' )->nullOnInvalid(),
            ],
        );
};
