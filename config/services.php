<?php

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Northrook\Symfony\Core\Services\EnvironmentService;

function fromRoot( string $set = '' ) : string {
	return '%kernel.project_dir%' . DIRECTORY_SEPARATOR . trim(
			str_replace( [ '\\', '/' ], DIRECTORY_SEPARATOR, $set ), DIRECTORY_SEPARATOR,
		) . DIRECTORY_SEPARATOR;
}

return static function ( ContainerConfigurator $container ) : void {

	$container->parameters()
	          ->set( 'env', '%kernel.environment%' )
	          ->set( 'dir.root', fromRoot() )
	          ->set( 'dir.public', fromRoot( "/public" ) )
	          ->set( 'dir.templates', fromRoot( "/templates" ) )
	          ->set( 'dir.cache', fromRoot( "/var/cache" ) )
	          ->set( 'dir.cache.latte', fromRoot( "/var/cache/latte" ) )
	          ->set( 'ttl.cache', 86400 )
	;

	$container->services()
	          ->set( 'core.environment_service', EnvironmentService::class )
	          ->args( [
		                  service( 'parameter_bag' ),
		                  service( 'logger' )->nullOnInvalid(),
	                  ] )
	          ->public()
	          ->alias( EnvironmentService::class, 'core.environment_service' )
	;

};