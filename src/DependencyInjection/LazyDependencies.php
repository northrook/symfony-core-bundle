<?php

namespace Northrook\Symfony\Core\DependencyInjection;

use Closure;
use Northrook\Logger\Log;

abstract class LazyDependencies
{
    /** @var array<string, object|Closure> */
    private array $serviceMap = [];

    /**
     * @param string  $service  The property name to retrieve.
     *
     * @return ?object
     */
    public function __get( string $service ) : ?object {
        return $this->getMappedService( $service );
    }

    /** {@see LazyDependencies} does not allow setting of properties. */
    public function __set( string $name, $service ) : void {}

    /**
     * Check if a service is present in the {@see serviceMap}.
     *
     * @param string  $service
     *
     * @return bool
     */
    public function __isset( string $service ) : bool {
        return $this->has( $service );
    }

    /**
     * @param array<string, object|Closure>  $services
     *
     * @return void
     */
    protected function setMappedService( array $services ) : void {

        foreach ( $services as $property => $service ) {

            if ( !is_string( $property ) || !is_object( $service ) ) {
                Log::Error(
                    'Eager service {service} does not have a matching {propertyName} in {class}',
                    [
                        'service'      => $service,
                        'propertyName' => $property,
                        'class'        => $this::class,
                    ],
                );
                $this->serviceMap[ $property ] = null;
                continue;
            }

            if ( $service instanceof Closure ) {
                $this->serviceMap[ $property ] = $service;
                continue;
            }

            if ( property_exists( $service, $property ) ) {
                $this->{$property} = $service;
            }
            else {
                Log::Error(
                    'Eager service {service} does not have a matching {propertyName} in {class}',
                    [
                        'service'      => $service,
                        'propertyName' => $property,
                        'class'        => get_class( $service ),
                    ],
                );
                $this->serviceMap[ $property ] = null;
            }
        }
    }

    /**
     * @param string  $service
     *
     * @return ?object
     */
    final public function getMappedService( string $service ) : ?object {

        $get = $this->serviceMap[ $service ] ?? null;

        if ( !$get ) {
            Log::Error(
                'Attempted to access unmapped service {service}.',
                [ 'service' => $service, 'serviceMap' => $this->serviceMap ],
            );
            return null;
        }

        if ( $get instanceof Closure ) {
            $this->serviceMap[ $service ] = ( $get )();
        }

        /** @var ?object */
        return $this->serviceMap[ $service ] ?? null;
    }

    /**
     * Check if a service is present in the {@see serviceMap}.
     *
     * @param string  $service
     *
     * @return bool
     */
    final public function has( string $service ) : bool {
        return array_key_exists( $service, $this->serviceMap );
    }
}