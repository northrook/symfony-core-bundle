<?php

namespace Northrook\Symfony\Core\DependencyInjection;

abstract class Facade {

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
        return Container::get(static::getServiceIdentifier());
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