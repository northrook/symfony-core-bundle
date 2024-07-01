<?php

declare( strict_types = 1 );

namespace Northrook\Symfony\Core;

use Northrook\Core\Env;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\HttpKernel\Bundle\AbstractBundle;
use function Northrook\Core\Function\normalizePath;


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

        $rootDir   = normalizePath( '%kernel.project_dir%' );
        $configDir = normalizePath( '%kernel.project_dir%/config' );

        $builder->setParameter( 'dir.root', $rootDir );
        $builder->setParameter( 'dir.config', $configDir );

        // $container->import( '../config/cache.php' );
        // $container->import( '../config/services.php' );
        // $container->import( '../config/facades.php' );
        // $container->import( '../config/controllers.php' );

        $autoConfigure = new AutoConfigure( $rootDir, $configDir );

        // Autoconfigure Notes
        // Look for .yaml files in config folder, remove them if adding .php version and vice versa
        // TODO : Autoconfigure Security
        // $this->autoconfigureRoutes();
    }

    public function boot() : void {
        parent::boot();
        new Env(
            $this->container->getParameter( 'kernel.environment' ),
            $this->container->getParameter( 'kernel.debug' ),
        );
        // DependencyInjection\Facade\Container::set( $this->container );
    }

    public function getPath() : string {
        return dirname( __DIR__ );
    }

    // private function autoconfigureRoutes() : void {
    //
    //     $apiController = new Path( $this->projectDir . '/config/routes/core.yaml' );
    //     $coreConfig    = [];
    //
    //     if ( $apiController->exists ) {
    //         if ( 'cli' === PHP_SAPI ) {
    //             echo Console::info( 'northrook.core.api', 'Config exists: ' . $apiController->value );
    //         }
    //         return;
    //     }
    //
    //     foreach ( SymfonyCoreBundle::ROUTES as $key => $value ) {
    //         $coreConfig[] = "$key:\n    resource: '{$value['resource']}'\n    prefix: {$value['prefix']}\n";
    //     }
    //
    //     $status = File::save(
    //         $this->projectDir . '/config/routes/core.yaml',
    //         implode( PHP_EOL, $coreConfig ),
    //     );
    //
    //     if ( 'cli' !== PHP_SAPI ) {
    //         return;
    //     }
    //
    //     if ( !$status ) {
    //         echo Console::error( 'northrook.core.api:', 'Config file not created: ' . $apiController->value );
    //     }
    //     else {
    //         echo Console::OK( 'northrook.core.api:', 'Config created: ' . $apiController->value );
    //     }
    // }
}