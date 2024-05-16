<?php

declare( strict_types = 1 );

namespace Northrook\Symfony\Core\DependencyInjection\Facade;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\ServiceLocator;


final class Container
{

    private static ContainerInterface $instance;

    private static ?ServiceLocator $serviceLocator = null;

    /**
     * Assign a container instance to the static container.
     *
     * @param ContainerInterface  $container
     */
    public static function set( ContainerInterface $container ) : void {
        Container::$instance       = $container;
        Container::$serviceLocator = $container->get(
            id              : 'core.service.locator',
            invalidBehavior : ContainerInterface::NULL_ON_INVALID_REFERENCE,
        );
    }

    /**
     * @template Service
     *
     * @param class-string<Service>  $className
     *
     * @return Service
     */
    public static function get( string $className ) : mixed {

        if ( Container::$instance->has( $className ) ) {
            return Container::$instance->get( $className );
        }

        if ( Container::$serviceLocator?->has( $className ) ) {
            return Container::$serviceLocator->get( $className );
        }

        return null;
    }

}