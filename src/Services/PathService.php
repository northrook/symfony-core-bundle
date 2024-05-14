<?php

namespace Northrook\Symfony\Core\Services;

use Northrook\Core\Cache;use Northrook\Support\Str;use Northrook\Types\Path;use Psr\Cache\InvalidArgumentException;use Psr\Log\LoggerInterface;use Symfony\Component\Cache\Adapter\TraceableAdapter;use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

// TODO: Support creating missing directories


final readonly class PathService
{

    private array $parameters;

    public function __construct(
        private  ParameterBagInterface $parameterBag,
        private  TraceableAdapter      $cache,
        private  ?LoggerInterface      $logger = null,
    ) {    }

    public function test( string $path = '' ) : string {
        return $path;
    }

    public function getParameter( string $name ) : ?string {
        return $this->getParameters()[$name] ?? null;
    }

    public function get( string $path  ) : ?string {
        try{
            return $this->cache->get( $path, $this->resolvePath( $path ) );
        }catch(InvalidArgumentException $e){
            $this->logger->Error(
                'The passed key, {key}, is somehow not a {type}. This really should not happen. Returning {return} instead.',
                ['key' => print_r($path, true),'type' => $path, 'return' => 'null', 'message' => $e->getMessage()],
            );
            return null;
        }
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

        if ( isset( $this->parameters ) ) {
            return $this->parameters;
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

        return $this->parameters = $parameters;
    }
}