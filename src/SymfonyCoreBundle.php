<?php

declare( strict_types = 1 );

namespace Northrook\Symfony\Core;

use Northrook\Core\Env;
use Northrook\Symfony\Core\DependencyInjection\Compiler\ApplicationAutoConfiguration;
use Northrook\Symfony\Core\DependencyInjection\Compiler\PathfinderServicePass;
use Northrook\Symfony\Core\EventListener\HttpExceptionListener;
use Northrook\Symfony\Core\EventSubscriber\LoggerIntegrationSubscriber;
use Symfony\Component\DependencyInjection\Compiler\PassConfig;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\HttpKernel\Bundle\AbstractBundle;
use function Northrook\Core\Function\normalizePath;
use function Symfony\Component\DependencyInjection\Loader\Configurator\service;


/**
 * @version 1.0 ☑️
 * @author  Martin Nielsen <mn@northrook.com>
 *
 * @link    https://github.com/northrook Documentation
 * @todo    Update URL to documentation : root of symfony-core-bundle
 */
final class SymfonyCoreBundle extends AbstractBundle
{
    public function build( ContainerBuilder $container ) : void {

        $projectDir = $container->getParameter( 'kernel.project_dir' );

        // Remove Symfony default .yaml config, create .php config
        $this->autoConfigure( "$projectDir/config" );

        parent::build( $container );

        // Provide the Pathfinder with directory and path parameters
        $container->addCompilerPass(
            pass : new PathfinderServicePass( $projectDir ),
            type : PassConfig::TYPE_OPTIMIZE,
        );
    }

    private function autoConfigure( string $configDir ) : void {
        ( new ApplicationAutoConfiguration( $configDir ) )
            ->createConfigPreload()
            ->createConfigRoutes()
            ->createConfigServices()
            ->createConfigControllerRoutes();
    }

    public function loadExtension(
        array                 $config,
        ContainerConfigurator $container,
        ContainerBuilder      $builder,
    ) : void {

        $services = $container->services();

        foreach (
            [
                'core.config.latte.autoRefresh' => false,
                'core.config.latte.cacheTTL'    => null,
            ] as $name => $value
        ) {
            $builder->setParameter( $name, $value );
        }

        foreach ( [
            'dir.root'          => '%kernel.project_dir%',
            'dir.var'           => '%dir.root%/var',
            'dir.cache'         => '%dir.var%/cache',
            'dir.cache.latte'   => '%dir.cache%/latte',
            'dir.manifest'      => '%dir.var%/manifest',
            'dir.config'        => '%dir.root%/config',
            'dir.src'           => '%dir.root%/src',
            'dir.assets'        => '%dir.root%/assets',
            'dir.public'        => '%dir.root%/public',
            'dir.templates'     => '%dir.root%/templates',
            'dir.public.assets' => '%dir.root%/public/assets',
            'dir.core.assets'   => dirname( __DIR__ ) . '/assets',
        ] as $name => $value ) {
            $builder->setParameter( $name, normalizePath( $value ) );
        }

        $services->set( HttpExceptionListener::class )
                 ->tag( 'kernel.event_listener', [ 'priority' => 100 ] )
                 ->args(
                     [
                         service( 'core.current_request' ),
                         service( 'logger' )->nullOnInvalid(),
                     ],
                 );

        /** # 📝
         * Current Request Service
         */
        $services->set( LoggerIntegrationSubscriber::class )
                 ->args( [ service( 'logger' )->nullOnInvalid() ], )
                 ->tag( 'kernel.event_subscriber' );


        $container->import( '../config/cache.php' );
        $container->import( '../config/autowire.php' );
        $container->import( '../config/latte.php' );
        $container->import( '../config/facades.php' );
        $container->import( '../config/controllers.php' );

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
        DependencyInjection\ServiceContainer::set( $this->container );
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