<?php

declare( strict_types = 1 );

namespace Northrook\Symfony\Core;

use Northrook\Symfony\Core\DependencyInjection\ControllerRegistrationPass;
use Northrook\Symfony\Core\DependencyInjection\SymfonyCoreExtension;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\ExtensionInterface;

/**
 * @version 1.0 ☑️
 * @author  Martin Nielsen <mn@northrook.com>
 *
 * @link    https://github.com/northrook Documentation
 * @todo    Update URL to documentation : root of symfony-core-bundle
 */
final class SymfonyCoreBundle extends Bundle
{

    public function getContainerExtension() : ?ExtensionInterface {
        return new SymfonyCoreExtension();
    }

    // public function loadExtension(
    //     array                 $config,
    //     ContainerConfigurator $container,
    //     ContainerBuilder      $builder,
    // ) : void {
    //
    //     $builder->set
    //
    //     // $this->loadExtension();
    //
    //     $container->import( '../config/services.php' );
    //
    // }

    public function load() : void {}

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