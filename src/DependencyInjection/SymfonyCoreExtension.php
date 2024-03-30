<?php

namespace Northrook\Symfony\Core\DependencyInjection;

use Northrook\Symfony\Core\Controller\CoreApiController;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

class SymfonyCoreExtension extends Extension
{

    public function load( array $configs, ContainerBuilder $container ) : void {
        $this->addAnnotatedClassesToCompile(
            [
                CoreApiController::class,
            ],
        );
    }

}