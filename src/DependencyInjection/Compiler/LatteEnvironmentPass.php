<?php

//--------------------------------------------------------------------
// Latte Environment Pass
//--------------------------------------------------------------------

declare( strict_types = 1 );

namespace Northrook\Symfony\Core\DependencyInjection\Compiler;

use Northrook\Latte;
use Northrook\Latte\Compiler\ComponentExtension;
use Northrook\Latte\Extension\ElementExtension;
use Northrook\Latte\Extension\FormatterExtension;
use Northrook\Latte\Extension\OptimizerExtension;
use Northrook\Latte\Extension\RenderExtension;
use Northrook\Latte\Runtime\ComponentAssetHandler;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

final class LatteEnvironmentPass implements CompilerPassInterface
{
    public function __construct( private string $projectDir ) {}

    public function process( ContainerBuilder $container ) : void {
        // Assign the path parameters to the Pathfinder service
        $latteBundle = $container->getDefinition( Latte::class );
        foreach ( $this->getTemplateDirectories( $container->getParameterBag() ) as $key => $dir ) {
            $latteBundle->addMethodCall( 'addTemplateDirectory', [ $dir, $key ] );
        }

        $latteBundle->addMethodCall(
            'addExtension', [
            // $container->getDefinition( IconManager::class ),
            $container->getDefinition( ComponentExtension::class ),
            $container->getDefinition( ElementExtension::class ),
            $container->getDefinition( RenderExtension::class ),
            $container->getDefinition( FormatterExtension::class ),
            $container->getDefinition( OptimizerExtension::class ),
        ],
        );

        $latteBundle->addMethodCall(
            'addPostprocessor', [
            [
                $container->getDefinition( ComponentAssetHandler::class ),
                'handleDocumentInjection',
            ],
        ],
        );
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