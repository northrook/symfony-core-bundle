<?php

/*-------------------------------------------------------------------/
   Application Config Pass

    - Removes default .yaml configuration files
    - Generates .php configuration files

/-------------------------------------------------------------------*/

declare( strict_types = 1 );

namespace Northrook\Symfony\Core\DependencyInjection\Compiler;

use Northrook\Symfony\AutoConfigure;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

final readonly class ApplicationConfigurationPass implements CompilerPassInterface
{
    private AutoConfigure $autoConfigure;

    public function __construct(
        private string $projectDir,
    ) {
        $this->autoConfigure = new AutoConfigure( $this->projectDir );
    }

    public function process( ContainerBuilder $container ) : void {
        $this->configPreload()
             ->configRoutes()
             ->configServices();
    }

    public function configPreload() : self {
        $this->autoConfigure->createConfigFile(
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

    public function configRoutes() : self {
        $this->autoConfigure->removeConfigFile( 'routes.yaml' );
        $this->autoConfigure->createConfigFile(
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

    public function configServices() : self {
        $this->autoConfigure->removeConfigFile( 'services.yaml' );
        $this->autoConfigure->createConfigFile(
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