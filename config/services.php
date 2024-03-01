<?php

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Northrook\Symfony\Core\EventSubscriber\LogAggregationOnTerminateSubscriber;
use Northrook\Symfony\Core\Services\EnvironmentService;
use Northrook\Symfony\Core\Services\PathfinderService;


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
		      ->set( 'core.service.environment', EnvironmentService::class )
	          ->args( [
		                  service( 'parameter_bag' ),
		                  service( 'logger' )->nullOnInvalid(),
	                  ] )
	          ->alias( EnvironmentService::class, 'core.service.environment' )
		//
		// Pathfinder Service
		      ->set( 'core.service.pathfinder', PathfinderService::class )
	          ->args( [
		                  service( 'parameter_bag' ),
		                  service( 'logger' )->nullOnInvalid(),
	                  ] )
	          ->public()
	          ->autowire()
	          ->alias( PathfinderService::class, 'core.service.pathfinder' )
		//
		// Log Aggregating Event Subscriber
		      ->set( LogAggregationOnTerminateSubscriber::class )
	          ->args( [
		                  service( 'logger' )->nullOnInvalid(),
	                  ] )
	          ->tag( 'kernel.event_subscriber' )//
	;


};