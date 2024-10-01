<?php

/*-------------------------------------------------------------------/
   Core Application
/-------------------------------------------------------------------*/

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Northrook\{Env, Latte, Settings};
use Northrook\Cache\MemoizationCache;
use Northrook\Symfony\Core\DependencyInjection\ApplicationInitializer;
use Northrook\Symfony\Core\Service\CurrentRequest;
use Northrook\Symfony\Service\DocumentService;
use Northrook\Symfony\Service\Toasts\ToastService;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Stopwatch\Stopwatch;

return static function( ContainerConfigurator $container ) : void {
    $core_services = [
        // Core
        CurrentRequest::class  => service( CurrentRequest::class ),
        Latte::class           => service( Latte::class ),
        DocumentService::class => service( DocumentService::class ),
        ToastService::class    => service( ToastService::class ),

        // Security
        TokenStorageInterface::class         => service( 'security.token_storage' ),
        AuthorizationCheckerInterface::class => service( 'security.authorization_checker' ),

        // Dev
        Stopwatch::class => service( 'debug.stopwatch' )->nullOnInvalid(),
        // SerializerInterface::class => service( 'serializer' ),
    ];

    $container->services()->set( 'core.service_locator' )
        ->tag( 'container.service_locator' )
        ->args( [$core_services] );

    // Initialize and preload core services
    $container->services()->set( ApplicationInitializer::class )
        ->tag( 'kernel.event_listener', ['priority' => 125] )
        ->args(
            [
                // Initialize the MemoizationCache
                service( MemoizationCache::class ),
                // Passed to a new ServiceContainer on invocation
                service( 'core.service_locator' ),
                // Passed to Log::setLogger on invocation
                service( 'logger' ),
            ],
        );

    $container->services()->defaults()->public()

            // Env
        ->set( Env::class )
        ->args( [param( 'kernel.environment' ), param( 'kernel.debug' )] )

            // Current Request Service
        ->set( CurrentRequest::class )
        ->args( [service( 'request_stack' ), service( 'http_kernel' )] )

            // Settings
        ->set( Settings::class )
        ->autowire()
        ->args(
            [
                [],
                false,
                null,
                '%kernel.environment%' !== 'prod',
                service( 'logger' )->nullOnInvalid(),
            ],
        );
};
