<?php

namespace Northrook\Symfony\Core\Services;

use Northrook\Support\Str;
use Psr\Cache\InvalidArgumentException;
use Psr\Log\LoggerInterface;
use Symfony\Component\Cache\Adapter\TraceableAdapter;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use function Northrook\Core\Function\normalizePath;

// TODO: Support creating missing directories

final readonly class PathfinderService
{
    public function __construct(
        private ParameterBagInterface $parameterBag,
        private TraceableAdapter      $cache,
        private ?LoggerInterface      $logger = null,
    ) {}

    public function getParameter( string $name ) : ?string {
        return $this->getParameters()[ $name ] ?? null;
    }

    // TODO : Support fetching nested templates
    public function get( string $path ) : ?string {

        try {
            $item = $this->cache->getItem( $this->key( $path ) );
        }
        catch ( InvalidArgumentException $e ) {
            $this->logger->Error(
                'The passed key, {key}, is somehow not a {type}. This really should not happen. Returning {return} instead.',
                [
                    'key'     => 'pathfinder.parameters', 'type' => 'string', 'return' => 'null',
                    'message' => $e->getMessage(),
                ],
            );
            return null;
        }

        if ( $item->isHit() ) {
            return $item->get();
        }

        $path = $this->resolvePath( $path );

        if ( $path !== null ) {
            $this->cache->save( $item->set( $path ) );
        }

        return $path;
    }

    private function resolvePath( string $path ) : ?string {

        $key = $this->key( $path );

        $separator = Str::contains( $path, [ '/', '\\' ], true, true );

        if ( $separator ) {

            [ $root, $path ] = explode( $separator[ 0 ], $path, 2 );

            $root = $this->getParameter( $root );

            if ( null === $root ) {
                $this->logger->Alert(
                    message : 'Failed getting container parameter {get}, the parameter does not exist. Using {value} instead.',
                    context : [ 'get' => $path, 'value' => 'null', ],
                );
                $root = '/';
            }


            $path = $root . $path;
        }
        else {
            $path = $this->getParameter( $path ) ?? $path;
        }

        if ( file_exists( $path ) ) {
            return normalizePath( $path );
        }

        $this->logger->Error(
            'Unable to resolve path {path}, the file or directory does not exist. The value was return raw, and not cached',
            [
                'cacheKey' => $key,
                'path'     => $path,
                'cache'    => $this->cache,
            ],
        );

        return null;
    }

    public function getParameters() : array {
        try {
            return $this->cache->get(
                'pathfinder.parameters', function () {

                $parameters = $this->directoryParameters();

                foreach ( $parameters as $key => $value ) {

                    // Simple sorting:
                    // Unset bundle-defined directories at their current position
                    // They will be appended to the array after all Symfony-defined directories
                    if ( str_starts_with( $key, 'dir' ) ) {
                        unset( $parameters[ $key ] );
                    }

                    $parameters[ $key ] = normalizePath( $value );
                }

                return $parameters;
            },
            );
        }
        catch ( InvalidArgumentException $e ) {
            $this->logger->Error(
                'The passed key, {key}, is somehow not a {type}. This really should not happen. Returning {return} instead.',
                [
                    'key'     => 'pathfinder.parameters', 'type' => 'string', 'return' => '[]',
                    'message' => $e->getMessage(),
                ],
            );
            return [];
        }
    }

    private function directoryParameters() : array {
        return array_filter(
            array    : $this->parameterBag->all(),
            callback : static fn ( $value, $key ) => is_string( $value ) && Str::contains( $key, [ 'dir', 'path' ] ),
            mode     : ARRAY_FILTER_USE_BOTH,
        );
    }

    private function key( string $path ) : string {
        return str_replace(
            [ '@', '{', '(', ')', '}', ':', '\\', '/' ], [ '%', '[', '[', ']', ']', '.', '_', '_' ], $path,
        );
    }
}