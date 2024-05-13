<?php

namespace Northrook\Symfony\Core\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerInterface;use Symfony\Component\DependencyInjection\ServiceLocator;

abstract class Facade {


    private static ContainerInterface $instance;
    private static ?ServiceLocator $serviceLocator = null;

    public static function container( ContainerInterface $container ) : void {
        Facade::$instance = $container;
        Facade::$serviceLocator = $container->get(
            id: 'core.service.locator',
             invalidBehavior: ContainerInterface::NULL_ON_INVALID_REFERENCE,
             );
    }


    /**
 * @param string | class-string $serviceId
*
* @return mixed
 */
    private static function get( string $serviceId ) : mixed {

        if (Facade::$instance->has($serviceId) ) {
            return Facade::$instance->get($serviceId);
        }

        if ( Facade::$serviceLocator?->has($serviceId) ) {
            return Facade::$serviceLocator->get($serviceId);
        }

        return null;
    }

    /**
     * Get the service identifier.
     *
     * @return string
     */
    abstract protected static function getServiceIdentifier() : string;

    /**
     * Get the service instance.
     *
     * @return mixed
     */
    public static function instance()
    {
        return Facade::get(static::getServiceIdentifier());
    }

    /**
     * Call the service.
     *
     * @param string $method
     * @param array  $arguments
     *
     * @return mixed
     */
    public static function __callStatic(string $method, array $arguments)
    {
        // Get the instance and call the method.
        return Facade::instance()->$method(...$arguments);
    }

}