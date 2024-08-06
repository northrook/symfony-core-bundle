<?php

namespace Northrook\Symfony\Core\Autowire;

use Psr\Cache\InvalidArgumentException;
use Psr\Log\LoggerInterface;
use Symfony\Component\Cache\Adapter\AdapterInterface;
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
     * @param bool    $clear
     * @param bool    $cached
     *
     * @return null|string
     */
    public function get( string $path, bool $clear = false, bool $cached = true ) : ?string {

        if ( !$cached ) {
            return $this->resolveParameterPath( $path );
        }

        try {
            $cache      = $this->cache->getItem( $this->key( $path ) );
            $cacheHit   = $cache->isHit();
            $cacheValue = $cacheHit ? $cache->get() : null;

            // Auto-clear empty items
            if ( $clear || !$cacheValue ) {
                $this->logger?->notice(
                    "The Pathfinder cache {key} has been cleared" .
                    ( $clear ? ' on request.' : ', as the cached value is empty.' ),
                    [ 'key' => $this->key( $path ) ],
                );
                $cacheHit = false;
            }
            else {
                return $cacheValue;
            }

        }
        catch ( InvalidArgumentException $exception ) {
            $this->logger?->error(
                'The Cache Adapter was provided an invalid key.',
                [ 'path' => $path, 'exception' => $exception ],
            );
            $cache = false;
        }

        $cacheValue = $this->resolveParameterPath( $path );

        // Ensure the resolved path actually exists
        if ( \file_exists( $cacheValue ) ) {
            $this->cache->saveDeferred(
                $cache->expiresAfter( 5 )
                      ->set( $cacheValue ),
            );
        }
        else {
            $this->logger->notice(
                'Unable to resolve path {path}, the file or directory does not exist. The value was return raw, and not cached',
                [
                    'cacheKey' => $cache->getKey(),
                    'path'     => $cacheValue,
                ],
            );

        }
        return $cacheValue;
    }

    private function resolveParameterPath( $path ) : ?string {

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
        return normalizePath( $resolvedValue );
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