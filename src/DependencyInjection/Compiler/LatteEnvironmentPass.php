<?php

declare( strict_types = 1 );

//--------------------------------------------------------------------
// Latte Environment Pass
//--------------------------------------------------------------------

namespace Northrook\Symfony\Core\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

final class LatteEnvironmentPass implements CompilerPassInterface
{
    public function process( ContainerBuilder $container ) : void {
        $container->getDefinition( 'latte.environment' )
                  ->addMethodCall(
                      method    : 'addPreprocessor',
                      arguments : [ $container->getDefinition( 'core.latte.preprocessor' ) ],
                  );
    }
}