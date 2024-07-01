<?php

declare( strict_types = 1 );

namespace Northrook\Symfony\Core;

use Northrook\Support\File;

/**
 * @internal
 * @author Martin Nielsen <mn@northrook.com>
 */
final class AutoConfigure
{

    private array $existingConfig = [];

    public function __construct(
        private readonly string $projectDirectory,
        private readonly string $configDirectory,
    ) {
        if ( 'cli' === PHP_SAPI ) {
            echo "This file is auto-configured with CLI.\n";
        }
        else {
            echo "We shouldn't be here";
        }
    }

    public function configRoutes() : self {

        $routesYaml = $this->configDirectory . DIRECTORY_SEPARATOR . 'routes.yaml';
        $routesPhp  = $this->configDirectory . DIRECTORY_SEPARATOR . 'routes.php';

        if ( File::exists( $routesYaml ) ) {
            $this->existingConfig[ 'routes.yaml' ] = File::read( $routesYaml );
            var_dump( $this->existingConfig[ 'routes.yaml' ] );
            File::remove( $routesYaml );
        }

        File::save(
            $routesPhp,
            <<<PHP
            <?php
            
            declare( strict_types = 1 );
            
            use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;
            
            return static function( RoutingConfigurator $routes ) : void {
                $routes->import( [
                    'path'		=> '../src/Controller/',
                    'namespace'	=> 'App\Controller',
                ], 'attribute' );
            };
            PHP,
        );

        return $this;
    }
}