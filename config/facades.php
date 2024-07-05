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

use Northrook\Latte\LatteBundle;
use Northrook\Symfony\Core\Autowire\CurrentRequest;
use Northrook\Symfony\Core\Autowire\Pathfinder;
use Northrook\Symfony\ToastManager;
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
                         HttpKernelInterface::class => service( 'http_kernel' ),
                         LoggerInterface::class     => service( 'logger' )->nullOnInvalid(),
                         Stopwatch::class           => service( 'debug.stopwatch' )->nullOnInvalid(),
                         // CsrfTokenManagerInterface::class     => service( 'security.csrf.token_manager' ),
                         // NotificationService::class => service( 'core.service.notification' ),

                         // Core
                         CurrentRequest::class      => service( 'core.current_request' ),
                         Pathfinder::class          => service( 'core.pathfinder' ),
                         LatteBundle::class         => service( 'core.latte_bundle' ),
                         ToastManager::class        => service( 'core.toast_manager' ),

                         // Dev
                         SerializerInterface::class => service( 'serializer' ),
                         // AuthorizationCheckerInterface::class => service( 'security.authorization_checker' ),
                         // TokenStorageInterface::class         => service( 'security.token_storage' ),
                     ],
                 ],
             )
             ->public();
};