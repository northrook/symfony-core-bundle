<?php

namespace Northrook\Symfony\Core\DependencyInjection;

abstract class Facade
{
    /**
     * Get the service identifier.
     *
     * @return class-string
     */
    protected const SERVICE = null;

    /**
* @var null|mixed The service instance, if {@see $cache} is true.
 */
    private static mixed $instance = null;

    /**
 * @var bool Whether to cache the service instance.
 */
    protected static bool $cache = true;

    /**
     * Call the service. 
     *
     * @param string  $method
     * @param array   $arguments
     *
     * @return mixed
     */
    public static function __callStatic( string $method, array $arguments ) {
        return static::service()->$method( ...$arguments );
    }

    /**
     * Get the service instance.
 *
 *  Will fetch the service instance from the container if {@see $cache} is false.
     *
     * @return mixed
     */
    protected static function service() : mixed {

        if ( true === static::$cache  ) {
            return static::$instance ??= Container::get( static::SERVICE );
        }

        return Container::get( static::SERVICE );
    }

    public static function clear() : void {
        static::$instance = null;
    }


}