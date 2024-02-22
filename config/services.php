<?php

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Northrook\Symfony\Core\Services\EnvironmentService;

function dir( string $set ) : string {
	return trim( str_replace( [ '\\','/'], DIRECTORY_SEPARATOR, $set ), DIRECTORY_SEPARATOR ) . DIRECTORY_SEPARATOR;
}

return static function (ContainerConfigurator $container): void {

	$root = mb_strtolower(dirname( __DIR__ ));

	$container->parameters()
			  ->set( 'env', '%kernel.environment%' )
			  ->set( 'dir.root', dir( $root ) )
			  ->set( 'dir.public', dir( "$root/public") )
			  ->set( 'dir.templates', dir( "$root/templates") )
			  ->set( 'dir.cache',  dir( "$root/var/cache") )
			  ->set( 'dir.cache.latte', dir( "$root/var/cache/latte") )
			  ->set( 'ttl.cache', 86400 );

	$container->services()
		->set( 'core.environment_service', EnvironmentService::class )
			->public();

};