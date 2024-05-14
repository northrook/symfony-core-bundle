<?php

declare( strict_types = 1 );

namespace Northrook\Symfony\Core;

use Northrook\Symfony\Core\DependencyInjection\Container;use Northrook\Symfony\Core\Support\Console;use Northrook\Types\Path;use Symfony\Component\DependencyInjection\ContainerBuilder;use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;use Symfony\Component\HttpKernel\Bundle\AbstractBundle;


/**
 * @version 1.0 ☑️
 * @author  Martin Nielsen <mn@northrook.com>
 *
 * @link    https://github.com/northrook Documentation
 * @todo    Update URL to documentation : root of symfony-core-bundle
 */
final class SymfonyCoreBundle extends AbstractBundle
{
    private const ROUTES = [
        'core.controller.api'      => [
            'resource' => '@SymfonyCoreBundle/config/routes/api.php',
            'prefix'   => '/api',
        ],
        'core.controller.admin'    => [
            'resource' => '@SymfonyCoreBundle/config/routes/admin.php',
            'prefix'   => '/admin',
        ],
        'core.controller.security' => [
            'resource' => '@SymfonyCoreBundle/config/routes/security.php',
            'prefix'   => '/',
        ],
        'core.controller.public'   => [
            'resource' => '@SymfonyCoreBundle/config/routes/public.php',
            'prefix'   => '/',
        ],
    ];

    private readonly string $projectDir;

    public function loadExtension(
        array                 $config,
        ContainerConfigurator $container,
        ContainerBuilder      $builder,
    ) : void {
        $this->projectDir ??= $builder->getParameterBag()->get( 'kernel.project_dir' );

        $container->import( '../config/services.php' );

        // Autoconfigure Notes
        // Look for .yaml files in config folder, remove them if adding .php version and vice versa
        // TODO : Autoconfigure Security
        $this->autoconfigureRoutes();
    }

    // TODO : Can we set the Static Container here ?
    public function build( ContainerBuilder $container ) : void {
        Cache::signalWarmupNeeded();
    }

    public function boot() : void {
        parent::boot();
        new Env(
            $this->container->getParameter( 'kernel.environment' ),
            $this->container->getParameter( 'kernel.debug' ),
        );
        Cache::setCacheDir( $this->container->getParameter( 'kernel.cache_dir' ) );
        SymfonyCoreFacade::set( $this->container );
        Container::set( $this->container );
    }

    public function getPath() : string {
        return dirname( __DIR__ );
    }

    private function autoconfigureRoutes() : void {

        $apiController = new Path( $this->projectDir . '/config/routes/core.yaml' );
        $coreConfig    = [];

        if ( $apiController->exists ) {
            if ( 'cli' === PHP_SAPI ) {
                echo Console::info( 'northrook.core.api', 'Config exists: ' . $apiController->value );
            }
            return;
        }

        foreach ( SymfonyCoreBundle::ROUTES as $key => $value ) {
            $coreConfig[] = "$key:\n    resource: '{$value['resource']}'\n    prefix: {$value['prefix']}\n";
        }

        $status = File::save(
            $this->projectDir . '/config/routes/core.yaml',
            implode( PHP_EOL, $coreConfig ),
        );

        if ( 'cli' !== PHP_SAPI ) {
            return;
        }

        if ( !$status ) {
            echo Console::error( 'northrook.core.api:', 'Config file not created: ' . $apiController->value );
        }
        else {
            echo Console::OK( 'northrook.core.api:', 'Config created: ' . $apiController->value );
        }
    }
}