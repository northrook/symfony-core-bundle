<?php

namespace Northrook\Symfony\Core;

use Northrook\Logger\Log;
use Northrook\Symfony\Core\Services\PathfinderService;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpKernel as App;

abstract class SymfonyCoreFacade
{
    protected static ContainerInterface $container;

    protected static function kernel() : App\Kernel {
        try {
            return self::$container->get( 'kernel' );
        }
        catch ( NotFoundExceptionInterface | ContainerExceptionInterface $e ) {
            Log::Emergency(
                'Failed getting container parameter {get}, the {get} does not exist. {action} triggered.',
                [
                    'get'       => 'kernel',
                    'action'    => 'E_USER_ERROR',
                    'exception' => $e,
                ],
            );
            trigger_error(
                'Failed getting container parameter "kernel", the parameter does not exist.',
                E_USER_ERROR,
            );
        }
    }

    protected static function pathfinderService() : PathfinderService {
        try {
            return self::$container->get( 'core.service.pathfinder' );
        }
        catch ( NotFoundExceptionInterface | ContainerExceptionInterface $e ) {
            Log::Emergency(
                'Failed getting container parameter {get}, the {get} does not exist. {action} triggered.',
                [
                    'get'       => 'core.service.pathfinder',
                    'action'    => 'E_USER_ERROR',
                    'exception' => $e,
                ],
            );
            trigger_error(
                'Failed getting container parameter "core.service.pathfinder", the parameter does not exist.',
                E_USER_ERROR,
            );
        }
    }

    protected static function parameterBag() : ParameterBagInterface {
        return self::kernel()->getContainer()->getParameterBag();
    }

    public static function set( ContainerInterface $container ) : void {


        if ( isset( self::$container ) ) {
            Log::Alert(
                'Attempting to override existing instance of {instance}. This is not allowed.',
                [
                    'instance' => 'ContainerInterface',
                    'file'     => __FILE__,
                    'class'    => self::class,
                ],
            );
            return;
        }

        self::$container = $container;
    }

}