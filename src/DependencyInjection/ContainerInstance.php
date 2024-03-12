<?php

namespace Northrook\Symfony\Core\DependencyInjection;

use Northrook\Logger\Log;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

/**
 * @internal
 * @author Martin Nielsen <mn@northrook.com>
 *
 */
final class ContainerInstance
{
    private static ContainerInterface $container;

    /**
     * Assign the container instance.
     *
     * @param ContainerInterface  $container
     *
     * @return void
     */
    public static function set( ContainerInterface $container ) : void {

        if ( isset( self::$container ) ) {
            Log::Alert(
                'Attempting to override existing instance of {instance}. This is not allowed.',
                [
                    'instance' => 'FacadesContainerInstance',
                    'file'     => __FILE__,
                    'class'    => self::class,
                ],
            );
            return;
        }

        self::$container = $container;
    }

    /**
     * @return ContainerInterface
     */
    public static function getInterface() : ContainerInterface {
        if ( !isset( self::$container ) ) {
            trigger_error( 'Container not set.', E_USER_ERROR );
        }
        return self::$container;
    }

    /**
     * @param string  $get  {@see ParameterBagInterface::get}
     *
     * @noinspection PhpMixedReturnTypeCanBeReducedInspection
     */
    public static function getService( string $get,
    ) : mixed {

        try {
            return self::getInterface()->get( $get );
        }
        catch ( NotFoundExceptionInterface | ContainerExceptionInterface $e ) {
            Log::Alert(
                'Failed getting container parameter {get}, the parameter does not exist. Returned {return} instead.',
                [
                    'get'       => $get,
                    'return'    => 'null',
                    'exception' => $e,
                ],
            );
            return null;
        }
    }


}