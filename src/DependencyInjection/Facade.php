<?php

namespace Northrook\Symfony\Core\DependencyInjection;

abstract class Facade
{
    private static mixed $instance = null;

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
     * Get the service identifier.
     *
     * @return string
     */
    abstract protected static function serviceId() : string;

    /**
     * Get the service instance.
     *
     * @return mixed
     */
    protected static function service() : mixed {

        if ( true === static::$cache  ) {
            return static::$instance ??= Container::get( static::serviceId() );
        }

        return Container::get( static::serviceId() );
    }

    public static function clear() : void {
        static::$instance = null;
    }


}