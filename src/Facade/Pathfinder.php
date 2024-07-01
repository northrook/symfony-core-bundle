<?php

namespace Northrook\Symfony\Core\Facade;

use Northrook\Support\Str;
use Northrook\Symfony\Core\DependencyInjection\Facade;
use Northrook\Symfony\Core\DependencyInjection\Facade\StaticInstance;
use Northrook\Symfony\Core\Services\PathfinderService;

/**
 * @method static string get( string $path )
 * @method static string getParameter( string $name )
 * @method static array  getParameters()
 */
final class Pathfinder extends Facade
{
    use StaticInstance;

    protected const SERVICE = PathfinderService::class;

    public static function normalize( string $path ) : string {
        return Str::normalizePath( $path );
    }

    /**
     * @param string | class-string  $path
     *
     * @return string %project.dir%/..
     */
    public static function root( string $path = '' ) : string {
        return $path;
    }

    /**
     * @param string | class-string  $path
     *
     * @return string %project.dir%/public/..
     */
    public static function public( string $path = '' ) : string {
        return $path;
    }


    /**
     * @param string | class-string  $path
     *
     * @return string %project.dir%/src/..
     */
    public static function src( string $path = '' ) : string {
        return $path;
    }


    /**
     * @param string | class-string  $path
     *
     * @return string %project.dir%/assets/..
     */
    public static function assets( string $path = '' ) : string {
        return $path;
    }

    /**
     * @param string | class-string  $path
     *
     * @return string %project.dir%/templates/..
     */
    public static function templates( string $path = '' ) : string {
        return $path;
    }

}