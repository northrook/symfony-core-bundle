<?php

declare( strict_types = 1 );

//--------------------------------------------------------------------
// Latte Environment Pass
//--------------------------------------------------------------------

namespace Northrook\Symfony\Core\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

final class LatteEnvironmentPass implements CompilerPassInterface
{
    public function __construct( private string $projectDir ) {}

    public function process( ContainerBuilder $container ) : void {
        // Assign the path parameters to the Pathfinder service
        $latteBundle = $container->getDefinition( 'core.latte_bundle' );
        foreach ( $this->getTemplateDirectories( $container->getParameterBag() ) as $key => $dir ) {
            $latteBundle->addMethodCall( 'addTemplateDirectory', [ $dir, $key ], );
        }
    }

    private function getTemplateDirectories( ParameterBagInterface $parameterBag ) : array {

        $parameters = \array_filter(
            array    : $parameterBag->all(),
            callback : fn ( $value, $key ) => \is_string( $value ) &&
                                              \str_contains( $key, 'dir' ) &&
                                              \str_contains( $key, 'templates' ) &&
                                              \str_starts_with( $value, $this->projectDir ),
            mode     : ARRAY_FILTER_USE_BOTH,
        );

        return \array_map( 'Northrook\normalizePath', $parameters );
    }
}