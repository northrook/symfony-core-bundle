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
     * Call the service. 
     *
     * @param string  $method
     * @param array   $arguments
     *
     * @return mixed
     */
    public static function __callStatic( string $method, array $arguments ) {
        return static::getService()->$method( ...$arguments );
    }

    /**
     * Get the service instance.
     * @return mixed
     */
    protected static function getService() : mixed {
        return Container::get( static::SERVICE );
    }



}