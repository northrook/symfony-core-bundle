<?php

//------------------------------------------------------------------
// config / Facades
//------------------------------------------------------------------

declare( strict_types = 1 );

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

// use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
// use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
// use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;

// use Northrook\Symfony\Latte\Environment;

use Northrook\Symfony\Core\Component\CurrentRequest;
use Northrook\Symfony\Core\Services\NotificationService;
use Northrook\Symfony\Core\Services\PathfinderService;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ServiceLocator;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Stopwatch\Stopwatch;

return static function ( ContainerConfigurator $container ) : void {

    $services = $container->services();

    $services->set( 'core.service.locator', ServiceLocator::class )
             ->tag( 'container.service_locator' )
             ->args(
                 [
                     [
                         RouterInterface::class     => service( 'router' ),
                         PathfinderService::class   => service( 'core.service.pathfinder' ),
                         LoggerInterface::class     => service( 'logger' )->nullOnInvalid(),
                         Stopwatch::class           => service( 'debug.stopwatch' )->nullOnInvalid(),
                         // Environment::class                   => service( 'latte.environment' ),
                         // CsrfTokenManagerInterface::class     => service( 'security.csrf.token_manager' ),
                         HttpKernelInterface::class => service( 'http_kernel' ),
                         NotificationService::class => service( 'core.service.notification' ),

                         // Dev
                         CurrentRequest::class      => service( 'core.component.request' ),
                         SerializerInterface::class => service( 'serializer' ),
                         // AuthorizationCheckerInterface::class => service( 'security.authorization_checker' ),
                         // TokenStorageInterface::class         => service( 'security.token_storage' ),
                     ],
                 ],
             )
             ->public();
};