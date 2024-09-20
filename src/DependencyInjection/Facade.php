<?php

declare( strict_types = 1 );

namespace Northrook\Symfony\Core\DependencyInjection;

/**
 * @property mixed $service [static]
 */
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
    public static function __callStatic( string $method, array $arguments )
    {
        return static::getService( static::SERVICE )?->$method( ...$arguments );
    }

    /**
     * @template Service
     *
     * @param ?class-string<Service>  $className
     *
     * @return Service
     */
    protected static function getService( ?string $className = null ) : mixed
    {
        if ( \property_exists( static::class, 'service' ) ) {
            return static::$service ??= ServiceContainer::get( $className ?? static::SERVICE );
        }
        // if (
        //         &&
        //         static::$service instanceof $className ?? static::SERVICE
        // ) {
        //     dump( 'Static Facade' );
        // }

        dump( 'Dynamic Facade' );
        return ServiceContainer::get( $className ?? static::SERVICE );
    }

}