<?php

namespace Northrook\Symfony\Core\DependencyInjection;

use Northrook\Symfony\Core\Controller\CoreApiController;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

class SymfonyCoreExtension extends Extension
{


    public function load( array $configs, ContainerBuilder $container ) : void {

        // $configs = $this->processConfiguration( new Configuration(), $configs );

        $locator = new FileLocator( __DIR__ . '/../Resources/config' );
        $loader  = new Loader\PhpFileLoader(
            $container,
            $locator,
        );
        $loader->load( 'services.php' );

        $this->addAnnotatedClassesToCompile(


            [
                CoreApiController::class,
            ],
        );
    }

}