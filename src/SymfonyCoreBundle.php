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

        foreach ( [
            'dir.root'          => '%kernel.project_dir%',
            'dir.config'        => '%kernel.project_dir%/config',
            'dir.src'           => '%kernel.project_dir%/src',
            'dir.templates'     => '%kernel.project_dir%/templates',
            'dir.assets'        => '%kernel.project_dir%/assets',
            'dir.public.assets' => '%kernel.project_dir%/public/assets',
            'dir.public'        => '%kernel.project_dir%/public',
            'dir.core.assets'   => dirname( __DIR__ ) . '/assets',
        ] as $name => $value ) {
            $builder->setParameter( $name, normalizePath( $value ) );
        }

        // $container->import( '../config/cache.php' );
        // $container->import( '../config/services.php' );
        // $container->import( '../config/facades.php' );
        // $container->import( '../config/controllers.php' );

        $autoConfigure = new AutoConfigure(
            $builder->getParameterBag()->get( 'kernel.project_dir' ),
        );

        $autoConfigure
            ->configPreload()
            ->configRoutes()
            ->configServices();

        // Autoconfigure Notes
        // Look for .yaml files in config folder, remove them if adding .php version and vice versa
        // TODO : Autoconfigure Security
        // $this->autoconfigureRoutes();
    }

    public function boot() : void {
        parent::boot();

        if ( PHP_SAPI === 'cli' ) {
            return;
        }

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