<?php

namespace Northrook\Symfony\Core;

use Northrook\Logger\Log;
use Northrook\Symfony\Core\Services\CurrentRequestService;
use Northrook\Symfony\Core\Services\PathfinderService;
use Northrook\Symfony\Latte\Core\Environment;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel as App;

abstract class SymfonyCoreFacade
{
    protected static ContainerInterface $container;

    protected static function getKernel() : App\Kernel {
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

    protected static function getRequestStack() : RequestStack {
        try {
            return self::$container->get( 'request_stack' );
        }
        catch ( NotFoundExceptionInterface | ContainerExceptionInterface $e ) {
            Log::Emergency(
                'Failed getting container parameter {get}, the {get} does not exist. {action} triggered.',
                [
                    'get'       => 'request_stack',
                    'action'    => 'E_USER_ERROR',
                    'exception' => $e,
                ],
            );
            trigger_error(
                'Failed getting container parameter "request_stack", the parameter does not exist.',
                E_USER_ERROR,
            );
        }
    }

    protected static function getCurrentRequestService() : CurrentRequestService {
        try {
            return self::$container->get( 'core.service.request' );
        }
        catch ( NotFoundExceptionInterface | ContainerExceptionInterface $e ) {
            Log::Emergency(
                'Failed getting container parameter {get}, the {get} does not exist. {action} triggered.',
                [
                    'get'       => 'core.service.request',
                    'action'    => 'E_USER_ERROR',
                    'exception' => $e,
                ],
            );
            trigger_error(
                'Failed getting container parameter "core.service.request", the parameter does not exist.',
                E_USER_ERROR,
            );
        }
    }

    protected static function getPathfinderService() : PathfinderService {
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

    protected static function getParameterBag() : ParameterBagInterface {
        return self::getKernel()->getContainer()->getParameterBag();
    }

    protected static function getLatteEnvironment() : ?Environment {
        try {
            return self::$container->get( 'latte.environment' );
        }
        catch ( NotFoundExceptionInterface | ContainerExceptionInterface $e ) {
            Log::Emergency(
                'Failed getting container parameter {get}, it does not exist. {action} triggered.',
                [
                    'get'       => 'latte.environment',
                    'action'    => 'null return',
                    'exception' => $e,
                ],
            );
            return null;
        }
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