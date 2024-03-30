<?php

namespace Northrook\Symfony\Core\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

class SymfonyCoreExtension extends Extension
{


    public function load( array $configs, ContainerBuilder $container ) : void {

        $locator = new FileLocator( dirname( __DIR__, 2 ) . '/config' );
        $loader  = new Loader\PhpFileLoader(
            $container,
            $locator,
        );
        $loader->load( 'services.php' );

        $this->addAnnotatedClassesToCompile(
            [
                'Northrook\\Symfony\\Core\\Controller\\CoreApiController',
            ],
        );
    }

}