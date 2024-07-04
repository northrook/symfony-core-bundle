<?php

namespace Northrook\Symfony\Core\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

final class PathfinderServicePass implements CompilerPassInterface
{

    public function process( ContainerBuilder $container ) : void {
        dump( $container->getParameterBag()->all() );
    }
}