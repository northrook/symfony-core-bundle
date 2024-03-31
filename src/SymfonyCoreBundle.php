<?php

declare( strict_types = 1 );

namespace Northrook\Symfony\Core;

use Northrook\Symfony\Core\Support\Console;
use Northrook\Types\Path;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\HttpKernel\Bundle\AbstractBundle;


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
        'core.controller.api' => [
            'resource' => '@SymfonyCoreBundle/config/routes/api.php',
            'prefix'   => '/api',
        ],
    ];

    private readonly string $projectDir;
    private bool            $booted = false;

    public function loadExtension(
        array                 $config,
        ContainerConfigurator $container,
        ContainerBuilder      $builder,
    ) : void {
        $this->projectDir ??= $builder->getParameterBag()->get( 'kernel.project_dir' );

        $container->import( '../config/services.php' );

        $this->autoconfigureRoutes();
        $this->booted = true;
    }

    public function boot() : void {
        parent::boot();

        if ( $this->container && !$this->booted ) {
            SymfonyCoreFacade::set( $this->container );
        }
    }

    public function getPath() : string {
        return dirname( __DIR__ );
    }

    private function autoconfigureRoutes() : void {
        if ( $this->booted ) {
            return;
        }

        $apiController = new Path( $this->projectDir . '/config/routes/core.yaml' );
        $coreConfig    = [];

        if ( $apiController->exists ) {
            echo Console::info( 'northrook.core.api', 'Config exists: ' . $apiController->value );
            return;
        }

        foreach ( self::ROUTES as $key => $value ) {
            $coreConfig[] = "$key:\n    resource: '{$value['resource']}'\n    prefix: {$value['prefix']}\n";
        }

        $status = File::save(
            $this->projectDir . '/config/routes/core.yaml',
            implode( PHP_EOL, $coreConfig ),
        );

        if ( !$status ) {
            echo Console::error( 'northrook.core.api:', 'Config file not created: ' . $apiController->value );
        }
        else {
            echo Console::OK( 'northrook.core.api:', 'Config created: ' . $apiController->value );
        }
    }
}