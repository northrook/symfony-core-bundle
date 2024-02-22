<?php

namespace Northrook\Symfony\Core\DependencyInjection;

use Exception;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader\PhpFileLoader;

final class SymfonyCoreExtension extends Extension
{

	/**
	 * @throws Exception
	 */
	public function load( array $configs, ContainerBuilder $container ) {
		$config = new PhpFileLoader( $container, new FileLocator( __DIR__ . '/../config' ) );
		$config->load( 'services.php' );
	}
}