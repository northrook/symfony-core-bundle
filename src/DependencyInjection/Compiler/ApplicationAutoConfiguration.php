<?php

/*-------------------------------------------------------------------/
   Application Config Pass

    - Removes default .yaml configuration files
    - Generates .php configuration files

/-------------------------------------------------------------------*/

declare( strict_types = 1 );

namespace Northrook\Symfony\Core\DependencyInjection\Compiler;

use Northrook\Symfony\AutoConfigure;
use Symfony\Component\Yaml\Yaml;

final class ApplicationAutoConfiguration extends AutoConfigure
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

    public function createConfigPreload() : self {
        $this->createConfigFile(
            'preload.php',
            <<<PHP
                <?php

                declare( strict_types = 1 );

                if ( file_exists( dirname( __DIR__ ) . '/var/cache/prod/App_KernelProdContainer.preload.php' ) ) {
                    opcache_compile_file( dirname( __DIR__ ) . '/var/cache/prod/App_KernelProdContainer.preload.php' );
                }
                PHP,
        );

        return $this;
    }

    public function createConfigControllerRoutes() : self {
        $this->createConfigFile( 'routes/core.yaml', Yaml::dump( $this::ROUTES ) );
        
        return $this;
    }

    public function createConfigRoutes() : self {
        $this->removeConfigFile( 'routes.yaml' );
        $this->createConfigFile(
            'routes.php',
            <<<PHP
                <?php

                declare( strict_types = 1 );

                use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;

                return static function ( RoutingConfigurator \$routes ) : void {
                    \$routes->import(
                        [
                            'path'      => '../src/Controller/',
                            'namespace' => 'App\Controller',
                        ],
                        'attribute',
                    );
                };
                PHP,
        );

        return $this;
    }

    public function createConfigServices() : self {
        $this->removeConfigFile( 'services.yaml' );
        $this->createConfigFile(
            'services.php',
            <<<PHP
                <?php

                declare( strict_types = 1 );

                use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

                return static function ( ContainerConfigurator \$container ) : void {

                    \$services = \$container->services();

                    // Defaults for App services.
                    \$services
                        ->defaults()
                        ->autowire()
                        ->autoconfigure();

                    \$services
                        // Make classes in src/ available to be used as services.
                        ->load( "App\\\\", __DIR__ . '/../src/' )
                        // We do not want to autowire DI, ORM, or Kernel classes.
                        ->exclude(
                            [
                                __DIR__ . '/../src/DependencyInjection/',
                                __DIR__ . '/../src/Entity/',
                                __DIR__ . '/../src/Kernel.php',
                            ],
                        );
                };
                PHP,
        );
        return $this;
    }
}