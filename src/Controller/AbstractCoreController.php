<?php

namespace Northrook\Symfony\Core\Controller;

use LogicException;
use Northrook\Symfony\Core\Enums\HTTP;
use Northrook\Symfony\Core\Services\EnvironmentService;
use Northrook\Symfony\Latte\Environment;
use Northrook\Symfony\Latte\Template;
use Psr\Container\ContainerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\Service\Attribute\Required;
use Symfony\Contracts\Service\Attribute\SubscribedService;

abstract class AbstractCoreController extends AbstractController
{
	protected ContainerInterface  $container;
	protected ?EnvironmentService $env;
	protected ?Environment        $latte;


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
			[
				'core.environment_service' => '?' . EnvironmentService::class,
				'core.latte'               => '?' . Environment::class,
				'core.template_parameters' => '?' . Template::class,
			],
		);
	}

	/**
	 * @param  string  $view  Template file or template string
	 * @param  object|array  $parameters
	 * @return string
	 */
	protected function latte(
		string         $view,
		object | array | null $parameters = null,
	) : string {

		if ( !$this->container->has( 'core.latte' ) ) {
			throw new LogicException(
				'You cannot use the "latte" or "latteResponse"  method if the Latte Bundle is not available.\nTry running "composer require northrook/symfony-latte-bundle".'
			);
		}

		$this->latte = $this->container->get( 'core.latte' );

		$this->latte->addExtension();
		$this->latte->addPrecompiler();

		$render = $this->latte->render( $view, $parameters ?? $this->container->get( 'core.template_parameters' ) );

		return $render;
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