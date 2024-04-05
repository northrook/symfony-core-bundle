<?php

namespace Northrook\Symfony\Core\DependencyInjection\Compiler;

use Northrook\Symfony\Core\Latte\LatteComponentPreprocessor;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class LatteEnvironmentPass implements CompilerPassInterface
{

    public function process( ContainerBuilder $container ) : void {

        $container->register( 'core.latte.preprocessor', LatteComponentPreprocessor::class );

        $container->getDefinition( 'latte.environment' )->addMethodCall(
            'addPreprocessor',
            [ $container->getDefinition( 'core.latte.preprocessor' ) ],
        );

    }
}