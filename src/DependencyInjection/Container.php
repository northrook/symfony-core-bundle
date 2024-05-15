<?php

namespace Northrook\Symfony\Core\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerInterface;use Symfony\Component\DependencyInjection\ServiceLocator;


final class Container {

    private static ContainerInterface $instance;

    private static ?ServiceLocator $serviceLocator = null;

    /**
     * Assign a container instance to the static container.
     *
     * @param ContainerInterface $container
     */
    public static function set( ContainerInterface $container ) : void {
        Container::$instance = $container;
        Container::$serviceLocator = $container->get(
            id: 'core.service.locator',
            invalidBehavior: ContainerInterface::NULL_ON_INVALID_REFERENCE,
        );
    }

    /**
     * @param class-string $serviceId
     *
     * @return mixed
     */
    public static function get( string $serviceId ) : mixed {

        if (Container::$instance->has($serviceId) ) {
            return Container::$instance->get($serviceId);
        }

        if ( Container::$serviceLocator?->has($serviceId) ) {
            return Container::$serviceLocator->get($serviceId);
        }

        return null;
    }

}