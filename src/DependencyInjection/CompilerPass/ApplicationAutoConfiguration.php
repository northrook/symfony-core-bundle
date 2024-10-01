<?php

/*-------------------------------------------------------------------/
   Application Config Pass

    - Removes default .yaml configuration files
    - Generates .php configuration files

/-------------------------------------------------------------------*/

declare(strict_types=1);

namespace Northrook\Symfony\Core\DependencyInjection\CompilerPass;

use Northrook\Symfony\Configurator\AutoConfigure;
use Symfony\Component\Yaml\Yaml;

final class ApplicationAutoConfiguration extends AutoConfigure
{
    private const array ROUTES
        = [
            'core.controller.api' => [
                'resource' => '@SymfonyCoreBundle/config/routes/api.php',
                'prefix'   => '/api',
            ],
            'core.controller.admin' => [
                'resource' => '@SymfonyCoreBundle/config/routes/admin.php',
                'prefix'   => '/admin',
            ],
            'core.controller.security' => [
                'resource' => '@SymfonyCoreBundle/config/routes/security.php',
                'prefix'   => '/',
            ],
            'core.controller.public' => [
                'resource' => '@SymfonyCoreBundle/config/routes/public.php',
                'prefix'   => '/',
            ],
        ];

    public function coreControllerRoutes() : self
    {
        $this->createFile( 'config/routes/core.yaml', Yaml::dump( $this::ROUTES ) );

        return $this;
    }

    public function configurePreload() : self
    {
        $this->createFile(
            'config/preload.php',
            <<<'PHP'
                <?php
                    
                declare( strict_types = 1 );
                    
                if ( \file_exists( \dirname( __DIR__ ) . '/var/cache/prod/App_KernelProdContainer.preload.php' ) ) {
                    \opcache_compile_file( \dirname( __DIR__ ) . '/var/cache/prod/App_KernelProdContainer.preload.php' );
                }
                PHP,
        );

        return $this;
    }

    public function removeDefaultRouteConfiguration() : self
    {
        $this->removeFile( 'config/routes.yaml' );
        return $this;
    }

    public function appControllerRouteConfiguration() : self
    {
        $this->removeFile( 'config/routes.yaml' );
        $this->createFile(
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

    public function createConfigServices() : self
    {
        $this->removeFile( 'config/services.yaml' );
        $this->createFile(
            'config/services.php',
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

    public function publicIndex() : self
    {
        $this->createFile(
            'public/index.php',
            <<<PHP
                <?php
                    
                declare( strict_types = 1 );
                    
                require_once \dirname( __DIR__ ) . '/vendor/autoload_runtime.php';
                    
                return function( array \$context ) : \App\Kernel
                {
                    return new \App\Kernel( \$context[ 'APP_ENV' ], (bool) \$context[ 'APP_DEBUG' ] );
                };
                PHP,
        );

        return $this;
    }

    public function appKernel() : self
    {
        $this->createFile(
            'src/Kernel.php',
            <<<PHP
                <?php
                    
                declare( strict_types = 1 );
                    
                namespace App;
                    
                use Symfony\Bundle\FrameworkBundle\Kernel as FrameworkBundle;
                use Symfony\Component\HttpKernel\Kernel as HttpKernel;
                    
                    
                final class Kernel extends HttpKernel
                {
                    use FrameworkBundle\MicroKernelTrait;
                }
                PHP,
        );
        return $this;
    }
}
