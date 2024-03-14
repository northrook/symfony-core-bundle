<?php

namespace Northrook\Symfony\Core;

use Northrook\Logger\Log;
use Northrook\Symfony\Core\Support\Str;
use Northrook\Types as Type;

final class Path extends SymfonyCoreFacade
{

    /**
     * Only valid Paths will be cached
     *
     * @var Type\Path[]
     */
    private static array $cache = [];

    /**
     * @var string[]
     */
    private static array $parametersCache;

    /**
     * @param string  $path  {@see ParameterBagInterface::get}
     *
     * @return Type\Path|string
     */
    public static function get( string $path ) : Type\Path | string {

        if ( isset( self::$cache[ $path ] ) ) {
            return self::$cache[ $path ];
        }

        $key = $path;

        $separator = Str::contains( $path, [ '/', '\\' ], true, true );

        if ( $separator ) {

            [ $root, $path ] = explode( $separator[ 0 ], $path, 2 );

            $root = self::getParameters( $root );

            if ( null === $root ) {
                Log::Alert(
                    message : 'Failed getting container parameter {get}, the parameter does not exist. Using {value} instead.',
                    context : [ 'get' => $path, 'value' => 'null', ],
                );
                $root = '/';
            }


            $path = $root . $path;
        }

        $path = new Type\Path( $path );

        if ( $path->isValid ) {
            return Path::$cache[ $key ] = $path;
        }

        Log::Error(
            'Unable to resolve path {path}, the file or directory does not exist. The returned {type::class} is invalid.',
            [
                'cacheKey'    => $path,
                'path'        => $path->value,
                'type'        => $path,
                'type::class' => $path::class,
                'cache'       => Path::$cache,
            ],
        );

        return $path;
    }

    private static function getParameters( string $get ) : array | string | null {

        if ( isset( self::$parametersCache ) ) {
            return self::$parametersCache[ $get ] ?? null;
        }

        self::$parametersCache = array_filter(
            array    : self::parameterBag()->all(),
            callback : static fn ( $value, $key ) => is_string( $value ) && str_contains( $key, 'dir' ),
            mode     : ARRAY_FILTER_USE_BOTH,
        );

        return self::$parametersCache[ $get ] ?? null;
    }
}