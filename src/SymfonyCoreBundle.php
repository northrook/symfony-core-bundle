<?php

declare( strict_types = 1 );

namespace Northrook\Symfony\Core;

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

    public function loadExtension(
        array                 $config,
        ContainerConfigurator $container,
        ContainerBuilder      $builder,
    ) : void {
        echo __METHOD__ . PHP_EOL;
        // $this->projectDir ??= $builder->getParameterBag()->get( 'kernel.project_dir' );
        //
        // $container->import( '../config/cache.php' );
        // $container->import( '../config/services.php' );
        // $container->import( '../config/facades.php' );
        // $container->import( '../config/controllers.php' );

        // Autoconfigure Notes
        // Look for .yaml files in config folder, remove them if adding .php version and vice versa
        // TODO : Autoconfigure Security
        // $this->autoconfigureRoutes();
    }

    public function boot() : void {
        parent::boot();
        echo __METHOD__ . PHP_EOL;
        // new Env(
        //     $this->container->getParameter( 'kernel.environment' ),
        //     $this->container->getParameter( 'kernel.debug' ),
        // );
        // DependencyInjection\Facade\Container::set( $this->container );
    }

    public function getPath() : string {
        return dirname( __DIR__ );
    }
}