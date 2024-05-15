<?php

namespace Northrook\Symfony\Core\DependencyInjection;

trait StaticFacadeInstance {

    /**
* @var null|mixed The service instance, if {@see $cache} is true.
 */
    private static mixed $instance = null;


    public static function getInstance(): mixed {

        if ( ! method_exists( parent::class, 'getService' ) ) {
            throw new \LogicException( 'The service must be defined in the container.' );
        }

        return self::$instance ?: self::$instance = parent::getService();
    }
}