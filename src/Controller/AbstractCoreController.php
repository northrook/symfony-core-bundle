<?php /** @noinspection PhpUnused */

namespace Northrook\Symfony\Core\Controller;

use LogicException;
use Northrook\Logger\Log;
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
	protected ContainerInterface  $container;
	protected ?EnvironmentService $env;
	protected ?Latte\Environment  $latte;


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

		if ( $container->has( 'core.environment_service' ) ) {
			$this->env = $container->get( 'core.service.environment' );
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

	/** Get the current session from the container session stack.
	 *
	 * * Pass a Request object to get the session from that request.
	 * * Pass nothing to get the current session
	 *
	 * @param  Request|null  $request  currentRequest by default
	 * @return SessionInterface|null The current session or null
	 * @version 1.0 ✅
	 */
	public function currentSession( ?Request $request = null ) : ?SessionInterface {
		$request ??= $this->currentRequest();

		return $request->hasSession() ? $request->getSession() : null;
	}

	/** Get the current route from the container request stack.
	 *
	 * @param  bool  $root  Return just the root route
	 * @return ?string The current route
	 */
	public function currentRoute( bool $root = false ) : ?string {
		$route = $this->currentRequest()->get( '_route' );

		return $root ? strstr( $route, ':', true ) : $route;
	}

	/** Returns the path being requested relative to the executed script.
	 *
	 * * The path info always starts with a /.
	 *
	 * @return string The raw path (i.e. not urlecoded)
	 */
	public function currentPathInfo() : string {
		return $this->currentRequest()->getPathInfo();
	}

	public function getRequestParameters( ?string $get = null ) : array | string | null {
		if ( $get === null ) {
			return $this->currentRequest()->request->all();
		}

		return $this->currentRequest()->request->get( $get );
	}


	/** Get the current route from the container request stack.
	 *
	 * * Pass bool true to get an array of all query parameters
	 * * Pass a string to get a single query parameter
	 * * Pass nothing to get the InputBag
	 *
	 *
	 * @param  string|bool|null  $get
	 * @return InputBag|string|array|null
	 */
	public function getRequestQuery( string | null | bool $get = null ) : InputBag | string | array | null {

		if ( $get === true ) {
			return $this->currentRequest()->query->all();
		}

		if ( $get ) {
			return $this->currentRequest()->query->get( $get );
		}

		return $this->currentRequest()->query;
	}

	/** Get the headers from the current request.
	 *
	 * * Pass a string to get a single header
	 * * Pass nothing to get the HeaderBag
	 *
	 * @param  string|null  $get
	 * @return HeaderBag|string|null
	 */
	public function getRequestHeaders( ?string $get = null ) : HeaderBag | string | null {

		if ( $get ) {
			return $this->currentRequest()->headers->get( $get );
		}

		return $this->currentRequest()->headers;
	}

}