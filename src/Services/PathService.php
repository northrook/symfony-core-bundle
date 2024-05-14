<?php

namespace Northrook\Symfony\Core\Services;

use Northrook\Core\Cache;use Northrook\Support\Str;use Northrook\Types\Path;use Psr\Log\LoggerInterface;use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

// TODO: Support creating missing directories


final readonly class PathService
{

    private array $parameters;


    public function __construct(
        private  ParameterBagInterface $parameterBag,
        private  Cache                 $cache,
        private  ?LoggerInterface      $logger = null,
    ) {
        $static = $this->cache::staticArrayCache(
            $this->parameterBag->get( 'kernel.cache_dir' ) .'/pathParameters.cache',
         );

        if ( $static->has( 'path.parameters' ) ) {
            $this->parameters = $static->get( 'path.parameters' );
        } else {
            $static->adapter->warmUp(
                ['path.parameters' => $this->getParameters()]
            );
        }

    }

    public function test( string $path = '' ) : string {
        return $path;
    }

    public function getParameter( string $name ) : ?string {
        return $this->parameters[ $name ] ?? null;
    }

    public function get( string $path = '' ) : ?string {

        $key = $this->key( $path );

        if ( $this->cache->has( $key  ) ) {
            return $this->cache->get( $key  );
        }

        return $this->cache->value( $key , $this->resolvePath( $path ) );
    }

    private function key( string $path ) : string {
        return "path:$path";
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
            return $path;
        }

        $this->logger->Error(
            'Unable to resolve path {path}, the file or directory does not exist. The value was return raw, and not cached',
            [
                'cacheKey' => $key,
                'path'     => $path,
                'cache'    => Cache::getCacheStore(),
            ],
        );

        return null;
    }

    private function getParameters() : array {

        if ( $this->cache->has( 'path.parameters' ) ) {
            return $this->cache->get( 'path.parameters' );
        }

        $parameters = array_filter(
            array    : $this->parameterBag->all(),
            callback : static fn ( $value, $key ) => is_string( $value ) && str_contains( $key, 'dir' ),
            mode     : ARRAY_FILTER_USE_BOTH,
        );

        foreach ( $parameters as $key => $value ) {

            // Simple sorting:
            // Unset bundle-defined directories at their current position
            // They will be appended to the array after all Symfony-defined directories
            if ( str_starts_with( $key, 'dir' ) ) {
                unset( $parameters[ $key ] );
            }

            $parameters[ $key ] = Path::normalize( $value );
        }

        return $this->cache->value( 'path:parameters', $parameters );
    }
}