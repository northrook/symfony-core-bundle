<?php

declare( strict_types = 1 );

namespace Northrook\Symfony\Core\DependencyInjection\Facade;

trait StaticInstance
{

    /**
     * @var null|mixed The service instance, if {@see $cache} is true.
     */
    private static mixed $instance = null;

    public static function getInstance() : mixed {

        if ( !method_exists( parent::class, 'getService' ) ) {
            throw new \LogicException( 'The service must be defined in the container.' );
        }

        return self::$instance ?: self::$instance = parent::getService();
    }
}