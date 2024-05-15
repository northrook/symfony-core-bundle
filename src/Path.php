<?php

namespace Northrook\Symfony\Core;

use Northrook\Core\Type\PathType;
use Northrook\Symfony\Core\DependencyInjection\Facade;
use Northrook\Symfony\Core\DependencyInjection\StaticFacadeInstance;
use Northrook\Symfony\Core\Services\PathfinderService;

/**
 * @method static string get( string $path )
 * @method static string getParameter( string $name )
 * @method static array  getParameters()
 */
final class Path extends Facade
{
    use StaticFacadeInstance;

    protected const SERVICE = PathfinderService::class;

    public static function normalize( string $path ) : string {
        return PathType::normalize( $path );
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