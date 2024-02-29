<?php

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Northrook\Symfony\Core\EventSubscriber\LogAggregationOnTerminateSubscriber;
use Northrook\Symfony\Core\Services\EnvironmentService;


return static function ( ContainerConfigurator $container ) : void {

	$fromRoot = function ( string $set = '' ) : string {
		return '%kernel.project_dir%' . DIRECTORY_SEPARATOR . trim(
				str_replace( [ '\\', '/' ], DIRECTORY_SEPARATOR, $set ), DIRECTORY_SEPARATOR,
			) . DIRECTORY_SEPARATOR;
	};

	$container->parameters()
	          ->set( 'env', '%kernel.environment%' )
	          ->set( 'dir.root', $fromRoot() )
	          ->set( 'dir.public', $fromRoot( "/public" ) )
	          ->set( 'dir.cache', $fromRoot( "/var/cache" ) )
	          ->set( 'ttl.cache', 86400 )
	;

	$container->services()
		//
		// Environment Service
		      ->set( 'core.environment_service', EnvironmentService::class )
	          ->args( [
		                  service( 'parameter_bag' ),
		                  service( 'logger' )->nullOnInvalid(),
	                  ] )
	          ->public()
	          ->alias( EnvironmentService::class, 'core.environment_service' )
		//
		// Log Aggregating Event Subscriber
		      ->set( LogAggregationOnTerminateSubscriber::class )
	          ->args( [
		                  service( 'logger' )->nullOnInvalid(),
	                  ] )
	          ->tag( 'kernel.event_subscriber' )//
	;


};