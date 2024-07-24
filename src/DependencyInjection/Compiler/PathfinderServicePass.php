<?php

namespace Northrook\Symfony\Core\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use function Northrook\normalizePath;

final readonly class PathfinderServicePass implements CompilerPassInterface
{

    public function __construct( private string $projectDir ) {}

    public function process( ContainerBuilder $container ) : void {
        // Assign the path parameters to the Pathfinder service
        $container->getDefinition( 'core.pathfinder' )
                  ->replaceArgument( 0, $this->getPathEntries( $container->getParameterBag() ) );
    }

    /**
     *  Get path parameters from the {@see ParameterBag}
     *
     * - Parses through all `string` parameters
     * - Only keys containing `dir` or `path` will be considered
     * - Only values starting with the {@see projectDir} are used
     *
     * @param ParameterBagInterface  $parameterBag
     *
     * @return array
     */
    private function getPathEntries( ParameterBagInterface $parameterBag ) : array {

        $parameters = array_filter(
            array    : $parameterBag->all(),
            callback : fn ( $value, $key ) => \is_string( $value ) &&
                                              ( \str_starts_with( $key, 'dir' ) ||
                                                \str_starts_with( $key, 'path', ) ) &&
                                              \str_starts_with( $value, $this->projectDir ),
            mode     : ARRAY_FILTER_USE_BOTH,
        );

        // Sort and normalise
        foreach ( $parameters as $key => $value ) {

            // Simple sorting; unsetting 'dir' and 'path' prefixed keys, appending them after all Symfony-defined directories
            if ( \str_starts_with( $key, 'dir' ) || \str_starts_with( $key, 'path' ) ) {
                unset( $parameters[ $key ] );
            }

            // Normalise each path
            $parameters[ $key ] = normalizePath( $value );
        }

        return $parameters;
    }
}