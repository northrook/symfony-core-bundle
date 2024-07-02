<?php

declare( strict_types = 1 );

namespace Northrook\Symfony\Core\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\ServiceLocator;


final class ServiceContainer
{

    private static ContainerInterface $instance;

    private static ?ServiceLocator $serviceLocator = null;

    /**
     * Assign a container instance to the static container.
     *
     * @param ContainerInterface  $container
     */
    public static function set( ContainerInterface $container ) : void {
        ServiceContainer::$instance       = $container;
        ServiceContainer::$serviceLocator = $container->get(
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

        if ( ServiceContainer::$instance->has( $className ) ) {
            return ServiceContainer::$instance->get( $className );
        }

        if ( ServiceContainer::$serviceLocator?->has( $className ) ) {
            return ServiceContainer::$serviceLocator->get( $className );
        }

        return null;
    }

}