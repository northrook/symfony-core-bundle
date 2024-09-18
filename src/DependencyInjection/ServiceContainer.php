<?php

declare( strict_types = 1 );

namespace Northrook\Symfony\Core\DependencyInjection;

use Symfony\Component\DependencyInjection\ServiceLocator;


final class ServiceContainer
{
    private static ServiceLocator $serviceLocator;

    /**
     * @param ServiceLocator  $serviceLocator
     */
    public function __construct( ?ServiceLocator $serviceLocator )
    {
        $this::$serviceLocator ??= $serviceLocator;
    }

    /**
     * @template Service
     *
     * @param class-string<Service>  $get
     *
     * @return Service
     */
    public static function get( string $get ) : mixed
    {
        if ( $get === ServiceLocator::class ) {
            return ServiceContainer::$serviceLocator;
        }

        return ServiceContainer::$serviceLocator->get( $get );
    }

}