<?php

namespace Northrook\Symfony\Core\DependencyInjection;

use Northrook\Symfony\Core\Controller\CoreApiController;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

final class ControllerRegistrationPass implements CompilerPassInterface
{

    public function process( ContainerBuilder $container ) : void {
        $this->registerCoreApiController( $container );
    }

    private function registerCoreApiController( ContainerBuilder $container ) : void {
        $container->register(
            'core.api.controller',
            CoreApiController::class,
        )->addTag( 'controller.service_arguments' );
    }
}