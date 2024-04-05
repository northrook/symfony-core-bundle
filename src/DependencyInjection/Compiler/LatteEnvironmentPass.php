<?php

namespace Northrook\Symfony\Core\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class LatteEnvironmentPass implements CompilerPassInterface
{

    public function process( ContainerBuilder $container ) : void {
        $latteEnvironment  = $container->getDefinition( 'latte.environment' );
        $lattePreprocessor = $container->getDefinition( 'core.latte.preprocessor' );
        // dd($lattePreprocessor);
        $latteEnvironment->addMethodCall(
            'addPreprocessor',
            [ $lattePreprocessor ],
        );;

    }
}