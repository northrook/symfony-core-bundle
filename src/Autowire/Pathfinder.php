<?php

namespace Northrook\Symfony\Core\Autowire;

use Northrook\Support\Str;
use Psr\Log\LoggerInterface;
use Symfony\Component\Cache\Adapter\AdapterInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Contracts\Cache\ItemInterface;
use function Northrook\Core\Function\normalizePath;

/**
 *
 */
final readonly class Pathfinder
{
    private array $directoryParameters;

    public function __construct(
        private ParameterBagInterface $parameterBag,
        private AdapterInterface      $cache,
        private ?LoggerInterface      $logger = null,
    ) {}

    /**
     * Return a parameter path, with prepend options.
     *
     * @param string  $path
     *
     * @return null|string
     */
    public function get( string $path ) : ?string {
        return $this->cache->get(
            key      : $this->key( $path ),
            callback : static fn ( ItemInterface $item ) => $this->resolveParameterPath( $path, $item ),
        );
    }


    private function resolveParameterPath( $path, ItemInterface $cache ) : ?string {

        $separator = Str::contains( $path, [ '/', '\\' ], true, true );

        // If we have a separator, check if the first substring is a parameter
        if ( $separator ) {
            [ $root, $path ] = explode( $separator[ 0 ], $path, 2 );
            $resolvedValue = $this->getParameterValue( $root ) . "/$path";
        }
        // Otherwise, just get the parameter value
        else {
            $resolvedValue = $this->getParameterValue( $path );
        }

        // Normalise the resolved value, assuming it is a path
        $resolvedValue = normalizePath( $resolvedValue );

        // Ensure the resolved path actually exists
        if ( file_exists( $resolvedValue ) ) {
            return $resolvedValue;
        }

        $this->logger->error(
            'Unable to resolve path {path}, the file or directory does not exist. The value was return raw, and not cached',
            [
                'cacheKey' => $cache->getKey(),
                'path'     => $resolvedValue,
            ],
        );

        return null;
    }

    public function getParameterValue( string $key ) : ?string {
        $this->directoryParameters ??= $this->getDirectoryParameters();
        $value                     = $this->directoryParameters[ $key ] ?? null;
        if ( null === $value ) {
            $this->logger->error(
                message : 'Failed getting container parameter {get}, the parameter does not exist or is assigned {value}. The value {value} has been returned.',
                context : [ 'get' => $key, 'value' => 'null', ],
            );
        }
        return $value;
    }

    private function getDirectoryParameters() : array {
        $parameters = array_filter(
            array    : $this->parameterBag->all(),
            callback : static fn ( $value, $key ) => is_string( $value ) && Str::contains( $key, [ 'dir', 'path' ] ),
            mode     : ARRAY_FILTER_USE_BOTH,
        );

        foreach ( $parameters as $key => $value ) {

            // Simple sorting:
            // Unset bundle-defined directories at their current position
            // They will be appended to the array after all Symfony-defined directories
            if ( str_starts_with( $key, 'dir' ) ) {
                unset( $parameters[ $key ] );
            }

            // Normalise each path
            $parameters[ $key ] = normalizePath( $value );
        }

        return $parameters;
    }

    private function key( string $path ) : string {
        return str_replace(
            [ '@', '{', '(', ')', '}', ':', '\\', '/' ], [ '%', '[', '[', ']', ']', '.', '_', '_' ], $path,
        );
    }
}