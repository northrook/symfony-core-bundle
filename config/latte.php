<?php

/*-------------------------------------------------------------------/
   config/latte
/-------------------------------------------------------------------*/

declare( strict_types = 1 );

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Northrook\Latte\Extension\CacheExtension;
use Northrook\Latte\Extension\FormatterExtension;
use Northrook\Latte\Extension\OptimizerExtension;
use Northrook\UI\Compiler\Latte\UiCompileExtension;


return static function( ContainerConfigurator $container ) : void
{
    $latte = $container->services();

    $latte
        ->defaults()
        ->autowire()
        ->autoconfigure()
    ;

    $latte
        ->set( UiCompileExtension::class )
        ->args(
            [
                service( 'core.latte.cache' )->nullOnInvalid(),
            ],
        )
    ;
    $latte->set( FormatterExtension::class );
    $latte->set( OptimizerExtension::class );

    $latte
        ->set( CacheExtension::class )
        ->args(
            [
                service( 'core.latte.cache' )->nullOnInvalid(),
                service( 'logger' )->nullOnInvalid(),
            ],
        )
    ;
};