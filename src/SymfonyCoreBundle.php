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

        if ( $apiController->exists ) {
            echo Console::info( 'northrook.core.api', 'Config exists: ' . $apiController->value );
            return;
        }

        $status = File::save(
            $this->projectDir . '/config/routes/core.yaml',
            <<<YAML
            northrook.core.api:
              resource: '@SymfonyCoreBundle/config/routes.php'
              prefix: /api
            YAML,
        );

        if ( !$status ) {
            echo Console::error( 'northrook.core.api:', 'Config file not created: ' . $apiController->value );
        }
        else {
            echo Console::OK( 'northrook.core.api:', 'Config created: ' . $apiController->value );
        }
    }
}