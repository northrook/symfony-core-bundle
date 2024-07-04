<?php

declare( strict_types = 1 );

//--------------------------------------------------------------------
// Latte Environment Pass
//--------------------------------------------------------------------

namespace Northrook\Symfony\Core\DependencyInjection\Compiler;

use Northrook\Support\Str;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

final class LatteEnvironmentPass implements CompilerPassInterface
{
    public function __construct( private string $projectDir ) {}


    public function process( ContainerBuilder $container ) : void {
        // Assign the path parameters to the Pathfinder service
        $container->getDefinition( 'core.latte_bundle' )
                  ->replaceArgument(
                      '$templateDirectories',
                      $this->getTemplateDirectories( $container->getParameterBag() ),
                  );
    }

    private function getTemplateDirectories( ParameterBagInterface $parameterBag ) : array {

        $parameters = array_filter(
            array    : $parameterBag->all(),
            callback : fn ( $value, $key ) => is_string( $value )
                                              && Str::contains( $key, [ 'dir', 'templates' ] )
                                              && str_starts_with( $value, $this->projectDir ),
            mode     : ARRAY_FILTER_USE_BOTH,
        );

        return array_map( 'Northrook\Core\Function\normalizePath', $parameters );
    }
}