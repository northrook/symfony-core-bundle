<?php

namespace Northrook\Symfony\Core\Services;

use Northrook\Support\Str;
use Northrook\Types\Path;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class PathfinderService
{

    /**
     * Only valid Paths will be cached
     *
     * @var Path[]
     */
    private static array $cache = [];

    /**
     * @var string[]
     */
    private static array $parametersCache;

    public function __construct(
        private readonly ParameterBagInterface $parameter,
        private readonly ?LoggerInterface      $logger = null,
    ) {}

    /**
     * @param string  $path  {@see ParameterBagInterface::get}
     *
     * @return Path
     */
    public function get( string $path ) : Path {


        if ( isset( PathfinderService::$cache[ $path ] ) ) {
            return PathfinderService::$cache[ $path ];
        }

        $key = $path;

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

        $path = new Path( $path );

        if ( $path->isValid ) {
            return PathfinderService::$cache[ $key ] = $path;
        }

        $this->logger->Error(
            'Unable to resolve path {path}, the file or directory does not exist. The returned {type::class} is invalid.',
            [
                'cacheKey'    => $path,
                'path'        => $path->value,
                'type'        => $path,
                'type::class' => $path::class,
                'cache'       => PathfinderService::$cache,
            ],
        );

        return $path;
    }

    private function getParameters() : array {

        if ( isset( PathfinderService::$parametersCache ) ) {
            return PathfinderService::$parametersCache;
        }

        $parameters = array_filter(
            array    : $this->parameter->all(),
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

        return PathfinderService::$parametersCache = $parameters;
    }

    public function getParameter( string $name ) : ?string {
        return $this->getParameters()[ $name ] ?? null;
    }

    public static function getCache( bool $parameterCache = false ) : array {

        if ( $parameterCache ) {
            return PathfinderService::$parametersCache ?? [];
        }

        return PathfinderService::$cache;
    }

    public static function clearCache() : void {
        PathfinderService::$cache = [];
    }
}