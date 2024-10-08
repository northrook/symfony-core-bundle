<?php

declare(strict_types=1);

namespace Northrook\Symfony\Core;

use Northrook\{Env, Settings};
use Northrook\Symfony\Core\Controller\EventController;
use Northrook\Symfony\Core\DependencyInjection\CompilerPass\{ApplicationAutoConfiguration,
    ApplicationSettingsPass,
    AssetCompilerPass,
    LatteEnvironmentPass};
use Northrook\Symfony\Core\Security\Authentication;
use Northrook\Symfony\Core\Service\CurrentRequest;
use Northrook\Symfony\Service\DocumentService;
use Override;
use Support\Normalize;
use Symfony\Component\DependencyInjection\Compiler\PassConfig;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\HttpKernel\Bundle\AbstractBundle;
use function Assert\isCLI;
use function Support\toString;
use function Symfony\Component\DependencyInjection\Loader\Configurator\service;

// use Northrook\Symfony\Core\ErrorHandler\HttpExceptionListener;

/**
 * @todo    Update URL to documentation : root of symfony-core-bundle
 * @author  Martin Nielsen <mn@northrook.com>
 *
 * @see    https://github.com/northrook Documentation
 * @version 1.0 ☑️
 */
final class SymfonyCoreBundle extends AbstractBundle
{
    /**
     * @param ContainerBuilder $container
     *
     * @return void
     */
    #[Override]
    public function build( ContainerBuilder $container ) : void
    {
        $projectDir = toString( $container->getParameter( 'kernel.project_dir' ) );

        // Remove Symfony default .yaml config, create .php config
        ( new ApplicationAutoConfiguration( $projectDir ) )
            ->configurePreload()
            ->createConfigServices()
            ->removeDefaultRouteConfiguration()
                // ->appControllerRouteConfiguration() // Should only run on-demand, as we may have no App/Controller present
            ->coreControllerRoutes()
            ->appKernel()
            ->publicIndex();

        parent::build( $container );

        // Provide the Application Settings and Env core parameters
        $container->addCompilerPass(
            pass : new ApplicationSettingsPass( $projectDir ),
            type : PassConfig::TYPE_OPTIMIZE,
        );

        // Locate, optimize, and cache core assets
        $container->addCompilerPass(
            pass : new AssetCompilerPass(),
            type : PassConfig::TYPE_OPTIMIZE,
        );

        // Provide the Pathfinder with directory and path parameters
        $container->addCompilerPass(
            pass : new LatteEnvironmentPass( $projectDir ),
            type : PassConfig::TYPE_OPTIMIZE,
        );
    }

    /**
     * @param array<array-key, mixed> $config
     * @param ContainerConfigurator   $container
     * @param ContainerBuilder        $builder
     *
     * @return void
     */
    #[Override]
    public function loadExtension(
        array                 $config,
        ContainerConfigurator $container,
        ContainerBuilder      $builder,
    ) : void {
        // Settings and Env
        $container->import( '../config/application.php' );
        $container->import( '../config/telemetry.php' );
        $container->import( '../config/controllers.php' );

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
            'dir.root'           => '%kernel.project_dir%',
            'dir.var'            => '%dir.root%/var',
            'dir.cache'          => '%dir.var%/cache',
            'dir.cache.latte'    => '%dir.cache%/latte',
            'dir.manifest'       => '%dir.var%/manifest',
            'dir.config'         => '%dir.root%/config',
            'dir.src'            => '%dir.root%/src',
            'dir.assets'         => '%dir.root%/assets',
            'dir.public'         => '%dir.root%/public',
            'dir.templates'      => '%dir.root%/templates',
            'dir.core.templates' => \dirname( __DIR__ ).'/templates',
            'dir.public.assets'  => '%dir.root%/public/assets',
            'dir.core.assets'    => \dirname( __DIR__ ).'/assets',
        ] as $name => $value ) {
            $builder->setParameter( $name, Normalize::path( $value ) );
        }

        $services
            ->set( EventController::class )
            ->tag( 'kernel.event_listener', ['priority' => 100] )
            ->tag( 'controller.service_arguments' )
            ->args(
                [
                    service( CurrentRequest::class ),
                    service( DocumentService::class ),
                    service( Authentication::class ),
                ],
            );

        $container->import( '../config/assets.php' );
        $container->import( '../config/cache.php' );
        $container->import( '../config/services.php' );
        $container->import( '../config/security.php' );
        $container->import( '../config/latte.php' );
    }

    #[Override]
    public function boot() : void
    {
        parent::boot();

        if ( isCLI() ) {
            return;
        }

        // Initialize the Env instance
        $this->container?->get( Env::class );

        // Initialize the Settings instance.
        $this->container?->get( Settings::class );
    }

    /**
     * Gets the Bundle directory path.
     *
     * @return string
     */
    public function getPath() : string
    {
        return \dirname( __DIR__ );
    }
}
