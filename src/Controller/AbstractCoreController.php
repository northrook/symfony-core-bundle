<?php

namespace Northrook\Symfony\Core\Controller;

use Northrook\Symfony\Core\Services\EnvironmentService;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Contracts\Service\Attribute\Required;
use Symfony\Contracts\Service\Attribute\SubscribedService;

abstract class AbstractCoreController extends AbstractController
{
	protected ContainerInterface  $container;
	protected ?EnvironmentService $env;

	/** Runs on container initialization.
	 *
	 * * Modified from the Symfony AbstractController
	 * * Initializes additional services
	 */
	#[Required]
	public function setContainer( ContainerInterface $container ) : ?ContainerInterface {
		$previous = $this->container ?? null;
		$this->container = $container;

		if ( $container->has( 'core.environment_service' ) ) {
			$this->env = $container->get( 'core.environment_service' );
		}

		return $previous;
	}

	/** Get subscribed services
	 *
	 * Subscribes to additional services:
	 * * core.environment_service
	 *
	 *
	 * @return string[]|SubscribedService[]
	 *  */
	public static function getSubscribedServices() : array {
		return array_merge(
			parent::getSubscribedServices(),
			[
				'core.environment_service' => '?' . EnvironmentService::class,
			],
		);
	}

	protected function latte(
		string         $template,
		object | array $parameters = [],
	) : string {
		return '';
	}

	protected function renderLatte(
		string         $template,
		object | array $parameters = [],
	) : string {
		return '';
	}


}