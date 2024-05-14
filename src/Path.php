<?php

namespace Northrook\Symfony\Core;

use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;use Symfony\Contracts\Cache\CacheInterface;

final readonly class Path {

    private ParameterBagInterface $parameterBag;
    private CacheInterface $cache;

    public function dependencyInjection(
        ParameterBagInterface $parameterBag,
        CacheInterface $cache,
    )  : void{
        $this->parameterBag = $parameterBag;
        $this->cache = $cache;

        dump( $this);
    }


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