<?php

namespace Northrook\Symfony\Core\Services;

use Northrook\Symfony\Core\Support\Str;
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
    private static array $pathfinderCache = [];

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


        if ( isset( self::$pathfinderCache[ $path ] ) ) {
            return self::$pathfinderCache[ $path ];
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
            return self::$pathfinderCache[ $key ] = $path;
        }

        $this->logger->Error(
            'Unable to resolve path {path}, the file or directory does not exist. The returned {type::class} is invalid.',
            [
                'cacheKey'    => $path,
                'path'        => $path->value,
                'type'        => $path,
                'type::class' => $path::class,
                'cache'       => self::$pathfinderCache,
            ],
        );

        return $path;
    }

    private function getParameter( string $name ) : ?string {

        if ( isset( self::$parametersCache ) ) {
            return self::$parametersCache[ $name ] ?? null;
        }

        self::$parametersCache = array_filter(
            array    : $this->parameter->all(),
            callback : static fn ( $value, $key ) => is_string( $value ) && str_contains( $key, 'dir' ),
            mode     : ARRAY_FILTER_USE_BOTH,
        );

        return self::$parametersCache[ $name ] ?? null;
    }

    public static function getCache() : array {
        return self::$pathfinderCache;
    }

    public static function clearCache() : void {
        self::$pathfinderCache = [];
    }
}