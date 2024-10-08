<?php

declare(strict_types=1);

// --------------------------------------------------------------------
// Latte Environment Pass
// --------------------------------------------------------------------

namespace Northrook\Symfony\Core\DependencyInjection\CompilerPass;

use Northrook\Latte;
use Northrook\Latte\Extension\{FormatterExtension, OptimizerExtension};
use Northrook\UI\Compiler\Latte\UiCompileExtension;
use Support\Normalize;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

final readonly class LatteEnvironmentPass implements CompilerPassInterface
{
    public function __construct( private string $projectDir ) {}

    public function process( ContainerBuilder $container ) : void
    {
        // Assign the path parameters to the Pathfinder service
        $latteBundle = $container->getDefinition( Latte::class );

        foreach ( $this->getTemplateDirectories( $container->getParameterBag() ) as $key => $dir ) {
            $latteBundle->addMethodCall( 'addTemplateDirectory', [$dir, $key] );
        }

        $latteBundle->addMethodCall(
            'addExtension',
            [
                // $container->getDefinition( IconManager::class ),
                $container->getDefinition( UiCompileExtension::class ),
                $container->getDefinition( FormatterExtension::class ),
                $container->getDefinition( OptimizerExtension::class ),
            ],
        );
    }

    private function getTemplateDirectories( ParameterBagInterface $parameterBag ) : array
    {
        $parameters = \array_filter(
            array    : $parameterBag->all(),
            callback : fn( $value, $key ) => \is_string( $value )
                                                 && \str_contains( $key, 'dir' )
                                                 && \str_contains( $key, 'templates' )
                                                 && \str_starts_with( $value, $this->projectDir ),
            mode     : ARRAY_FILTER_USE_BOTH,
        );

        return \array_map( [Normalize::class, 'path'], $parameters );
    }
}
