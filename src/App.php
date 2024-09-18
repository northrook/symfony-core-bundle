<?php

namespace Northrook\Symfony\Core;

use Symfony\Component\DependencyInjection\ServiceLocator;


class App
{

    private static ServiceLocator $serviceLocator;

    public function __construct( ?ServiceLocator $serviceLocator = null )
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
    public static function serviceContainer( string $get ) : mixed
    {
        if ( $get === ServiceLocator::class ) {
            return static::$serviceLocator;
        }

        if ( static::$serviceLocator?->has( $get ) ) {
            return static::$serviceLocator->get( $get );
        }

        return null;
    }

    /**
     * The {@see \Northrook\Symfony\Core\App} class should not be cloned.
     *
     * @return void
     */
    private function __clone() : void {}
}