<?php

namespace Northrook\Symfony\Core\Autowire;

use Psr\Log\LoggerInterface;
use Symfony\Component\Cache\Adapter\AdapterInterface;
use Symfony\Contracts\Cache\ItemInterface;
use function Northrook\normalizePath;
use function Northrook\stringContains;

/**
 *
 */
final readonly class Pathfinder
{

    public function __construct(
        public array             $directories,
        private AdapterInterface $cache,
        private ?LoggerInterface $logger = null,
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
            callback : fn ( ItemInterface $item ) => $this->resolveParameterPath( $path, $item ),
        );
    }
    
    private function resolveParameterPath( $path, ItemInterface $cache ) : ?string {

        $separator = stringContains( $path, [ '/', '\\' ], true, true );

        // If we have a separator, check if the first substring is a parameter
        if ( $separator ) {
            [ $root, $path ] = explode( $separator, $path, 2 );
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

        return $resolvedValue;
    }

    public function getParameterValue( string $key ) : ?string {
        $value = $this->directories[ $key ] ?? null;
        if ( null === $value ) {
            $this->logger->error(
                message : 'Failed getting container parameter {get}, the parameter does not exist or is assigned {value}. The value {value} has been returned.',
                context : [ 'get' => $key, 'value' => 'null', ],
            );
        }
        return $value;
    }

    private function key(
        string $path,
    ) : string {
        return str_replace(
            [ '@', '{', '(', ')', '}', ':', '\\', '/' ], [ '%', '[', '[', ']', ']', '.', '_', '_' ], $path,
        );
    }
}