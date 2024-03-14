<?php

namespace Northrook\Symfony\Core;

use Northrook\Logger\Log;
use Northrook\Symfony\Core\Support\Str;
use Northrook\Types\Path;

final class Get extends SymfonyCoreFacade
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

    /**
     * @param string  $get  {@see ParameterBagInterface::get}
     *
     * @return Path|string
     */
    public static function path( string $get ) : Path | string {


        if ( isset( self::$pathfinderCache[ $get ] ) ) {
            return self::$pathfinderCache[ $get ];
        }

        $key = $get;

        $separator = Str::contains( $get, [ '/', '\\' ], true, true );

        if ( $separator ) {

            [ $root, $get ] = explode( $separator[ 0 ], $get, 2 );

            $root = self::getParameters( $root );

            if ( null === $root ) {
                Log::Alert(
                    message : 'Failed getting container parameter {get}, the parameter does not exist. Using {value} instead.',
                    context : [ 'get' => $get, 'value' => 'null', ],
                );
                $root = '/';
            }


            $get = $root . $get;
        }
        else {
            $get = self::getParameters( $get ) ?? $get;
        }

        $get = new Path( $get );

        if ( $get->isValid ) {
            return Get::$pathfinderCache[ $key ] = $get;
        }

        Log::Error(
            'Unable to resolve path {path}, the file or directory does not exist. The returned {type::class} is invalid.',
            [
                'cacheKey'    => $get,
                'path'        => $get->value,
                'type'        => $get,
                'type::class' => $get::class,
                'cache'       => Get::$pathfinderCache,
            ],
        );

        return $get;
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