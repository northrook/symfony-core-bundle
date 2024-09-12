<?php

declare( strict_types = 1 );

namespace Northrook\Symfony\Core\DependencyInjection;

trait StaticFacade
{
    const string SERVICE = '';

    /**
     * @var mixed The service instance for this {@see Facade}.
     */
    private static mixed $instance = null;

    /**
     * @template Service
     *
     * @param ?class-string<Service>  $className
     *
     * @return Service
     */
    protected static function getService( ?string $className = null ) : mixed
    {
        if ( !\method_exists( static::class, 'getService' ) ) {
            throw new \LogicException( 'The service must be defined in the container.' );
        }

        return self::$instance ??= static::getService( $className ?? static::SERVICE );
    }
}