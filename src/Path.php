<?php

namespace Northrook\Symfony\Core;

use Northrook\Symfony\Core\DependencyInjection\Facade;use Northrook\Symfony\Core\Services\PathService;

/**
 * @method static string test( string $path = ''  )
 */
final class Path extends Facade {

    protected const NAMESPACE = PathService::class;

    /**
* @param string | class-string $path
*
*  @return string %project.dir%/..
 */
    public static function root( string $path = ''  ) : string {
        return $path;
    }

    /**
* @param string | class-string $path
*
*  @return string %project.dir%/public/..
 */
    public static function public( string $path = ''  ) : string {
        return $path;
    }


    /**
* @param string | class-string $path
*
* @return string %project.dir%/src/..
 */
    public static function src( string $path = ''  ) : string {
        return $path;
    }



/**
* @param string | class-string $path
*
* @return string %project.dir%/assets/..
 */
    public static function assets( string $path = ''  ) : string {
        return $path;
    }

/**
* @param string | class-string $path
*
* @return string %project.dir%/templates/..
 */
    public static function templates( string $path = ''  ) : string {
        return $path;
    }


}