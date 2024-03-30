<?php

declare( strict_types = 1 );

namespace Northrook\Symfony\Core;

use Northrook\Symfony\Core\DependencyInjection\ControllerRegistrationPass;
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

    public function loadExtension(
        array                 $config,
        ContainerConfigurator $container,
        ContainerBuilder      $builder,
    ) : void {

        $container->import( '../config/services.php' );

    }


    public function build( ContainerBuilder $container ) : void {
        parent::build( $container );
        $container->addCompilerPass( new ControllerRegistrationPass() );
    }

    public function boot() : void {
        parent::boot();
        SymfonyCoreFacade::set( $this->container );
    }

    public function getPath() : string {
        return dirname( __DIR__ );
    }
}