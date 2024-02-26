<?php

namespace Northrook\Symfony\Core\Controller;

use Northrook\Symfony\Core\Enums\Status;
use Northrook\Symfony\Core\Services\EnvironmentService;
use Psr\Container\ContainerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
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
	 * @return string[]|SubscribedService[]
	 *  */
	public static function getSubscribedServices() : array {
		return array_merge(
			parent::getSubscribedServices(),
			[ 'core.environment_service' => '?' . EnvironmentService::class, ],
		);
	}

	/**
	 * @param  string  $view  Template file or template string
	 * @param  object|array  $parameters
	 * @return string
	 */
	protected function latte(
		string         $view,
		object | array $parameters = [],
	) : string {
		return 'This is a dummy template render';
	}

	protected function latteResponse(
		string         $view,
		object | array $parameters = [],
		int            $status = Response::HTTP_OK,
		array          $headers = [],
	) : Response {
		return new Response(
			content : $this->latte( $view, $parameters ),
			status  : $status,
			headers : $headers,
		);
	}


}