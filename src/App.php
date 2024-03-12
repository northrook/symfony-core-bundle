<?php

namespace Northrook\Symfony\Core;

use JetBrains\PhpStorm\ExpectedValues;
use Northrook\Logger\Log;
use Northrook\Types\Path;

final class App extends Facades\AbstractFacade
{

    /**
     * @var Path[] // Only valid Paths will be cached
     */
    private static array $pathfinderCache = [];

    public static function env(
        #[ExpectedValues( [ 'dev', 'prod', 'debug' ] )]
        string $is,
    ) : bool {
        if ( self::kernel() === null ) {
            Log::Alert(
                'Failed checking if {call} is {is}, as {kernel} is {status}. Returned {return} instead.',
                [
                    'is'     => $is,
                    'call'   => 'App::env',
                    'kernel' => 'App::kernel',
                    'status' => 'null',
                    'return' => 'false',
                ],
            );
            return false;
        }
        return match ( $is ) {
            'dev'   => App::kernel()->getEnvironment() == 'dev',
            'prod'  => App::kernel()->getEnvironment() == 'prod',
            'debug' => App::kernel()->isDebug(),
            default => false,
        };
    }


    /**
     * @param string       $root  {@see ParameterBagInterface::get}
     * @param string|null  $path
     *
     * @return string
     */
    public static function pathfinder(
//        #[ExpectedValues( self::KERNEL_DIR )]
        string  $root,
        ?string $path = null,
    ) : string {

        $dir  = App::parameterBag( $root );
        $path = App::pathfinderResolver( $dir, $path );

        return $path->value;
    }

    /**
     * @param string       $root
     * @param string|null  $path
     *
     * @return Path
     */
    private static function pathfinderResolver( string $root, ?string $path ) : Path {

        $key = $root . ( $path ? '/' . $path : '' );

        if ( !isset( App::$pathfinderCache[ $key ] ) ) {

            $pathfinder = new Path( $root );

            if ( $path ) {
                $pathfinder->add( $path );
            }

            if ( $pathfinder->isValid ) {
                return App::$pathfinderCache[ $key ] = $pathfinder;
            }

            Log::Error(
                'Unable to resolve path {path}, the file or directory does not exist. The returned {type::class} is invalid.',
                [
                    'cacheKey'    => $key,
                    'path'        => $pathfinder->value,
                    'type'        => $pathfinder,
                    'type::class' => $pathfinder::class,
                    'cache'       => App::$pathfinderCache,
                ],
            );

            return $pathfinder;

        }

        return App::$pathfinderCache[ $key ];
    }


}