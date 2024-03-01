<?php /** @noinspection PhpUnused */

namespace Northrook\Symfony\Core\Controller;

use LogicException;
use Northrook\Logger\Log;
use Northrook\Symfony\Core\Services\CurrentRequestService;
use Northrook\Symfony\Core\Services\EnvironmentService;
use Northrook\Symfony\Latte;
use Northrook\Symfony\Latte\Template;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\HeaderBag;
use Symfony\Component\HttpFoundation\InputBag;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Contracts\Service\Attribute\Required;
use Symfony\Contracts\Service\Attribute\SubscribedService;


/**
 * Abstract Core Controller
 *
 * * Integrates {@see Latte\Environment} from `northrook/symfony-latte-bundle`
 *
 * @version 0.1.0 ☑️
 * @author Martin Nielsen <mn@northrook.com>
 */
abstract class AbstractCoreController extends AbstractController
{
	protected ContainerInterface    $container;
	protected CurrentRequestService $request;
	protected ?EnvironmentService   $env;
	protected ?Latte\Environment    $latte;


	/** Runs on container initialization.
	 *
	 * * Modified from the Symfony AbstractController
	 * * Initializes additional services
	 *
	 * @throws ContainerExceptionInterface
	 * @throws NotFoundExceptionInterface
	 */
	#[Required]
	public function setContainer( ContainerInterface $container ) : ?ContainerInterface {
		$previous = $this->container ?? null;
		$this->container = $container;

		if ( $container->has( 'core.service.environment' ) ) {
			$this->env = $container->get( 'core.service.environment' );
		}
		if ( $container->has( 'core.service.request' ) ) {
			$this->request = $container->get( 'core.service.request' );
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
				'core.service.request'     => '?' . CurrentRequestService::class,
				'core.service.environment' => '?' . EnvironmentService::class,
				'core.latte'               => '?' . Latte\Environment::class,
				'core.template_parameters' => '?' . Template::class,
			],
		);
	}

	/**
	 * @param  string  $view  Template file or template string
	 * @param  object|array|null  $parameters
	 * @return string
	 * @throws ContainerExceptionInterface
	 * @throws NotFoundExceptionInterface
	 */
	protected function latte(
		string                $view,
		object | array | null $parameters = null,
	) : string {

		if ( !$this->container->has( 'core.latte' ) ) {
			throw new LogicException(
				'You cannot use the "latte" or "latteResponse" method if the Latte Bundle is not available.\nTry running "composer require northrook/symfony-latte-bundle".'
			);
		}

		$this->latte = $this->container->get( 'core.latte' );

		$this->latte->addExtension();
		$this->latte->addPrecompiler();

		return $this->latte->render(
			template   : $view,
			parameters : $parameters,
		);
	}

	/**
	 * @param  string  $view
	 * @param  object|array  $parameters
	 * @param  int  $status
	 * @param  array  $headers
	 * @return Response
	 * @throws ContainerExceptionInterface
	 * @throws NotFoundExceptionInterface
	 */
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


	/** Get the current request from the container request stack.
	 *
	 * * Will return the current request if it exists, otherwise it will return a new request.
	 *
	 * @return Request The current request
	 * @version 1.0 ✅
	 * @uses    \Symfony\Component\HttpFoundation\RequestStack
	 */
	public function currentRequest() : Request {
		try {
			return $this->container->get( 'request_stack' )->getCurrentRequest();
		}
		catch ( NotFoundExceptionInterface | ContainerExceptionInterface ) {
			if ( $this->env->dev ) {
				Log::Warning(
					'Could not find the current request. Returned new {return}.',
					[
						'return' => 'Request()',
						'class'  => Request::class,
					],
				);
			}
			return new Request();
		}

	}

}