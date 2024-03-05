<?php /** @noinspection PhpUnused */

namespace Northrook\Symfony\Core\Controller;

use LogicException;
use Northrook\Symfony\Core\Components\LatteComponentPreprocessor;
use Northrook\Symfony\Core\Services\CurrentRequestService;
use Northrook\Symfony\Core\Services\EnvironmentService;
use Northrook\Symfony\Latte;
use Northrook\Symfony\Latte\Template;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
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
				'core.latte.preprocessor'  => '?' . LatteComponentPreprocessor::class,
				'latte.environment'        => '?' . Latte\Environment::class,
				'latte.core.extension'     => '?' . Template::class,
			],
		);
	}

	/**
	 * Run at the very start of the {@see Latte\Environment} render chain.
	 *
	 * @return void
	 */
	protected function __onLatteRender() : void {}

	/**
	 * @throws ContainerExceptionInterface
	 * @throws NotFoundExceptionInterface
	 */
	protected function getLatte() : Latte\Environment {

		if ( !$this->container->has( 'latte.environment' ) || !$this->container->has( 'core.latte.preprocessor' ) ) {
			throw new LogicException(
				'You cannot use the "latte" or "latteResponse" method if the Latte Bundle is not available.\nTry running "composer require northrook/symfony-latte-bundle".'
			);
		}

		$this->latte ??= $this->container->get( 'latte.environment' );

		$this->latte->addExtension();
		$this->latte->addPreprocessor( $this->container->get( 'core.latte.preprocessor' ) );


		return $this->latte;
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

		$this->latte ??= $this->getLatte();

		$this->__onLatteRender();

		return $this->latte->render(
			template   : $view,
			parameters : $parameters,
		);
	}

	/**
	 * @throws ContainerExceptionInterface
	 * @throws NotFoundExceptionInterface
	 */
	protected function modalResponse(
		string         $view,
		object | array $parameters = [],
		int            $status = Response::HTTP_OK,
		array          $headers = [],
		array          $attributes = [],
		// UI\Button $button -  from Latte Components
	) : Response {

		$options = [
			'Template-Type' => 'modal', // TODO: [?] as Enum from Latte Components
		];

		$content = $this->latte( $view, $parameters );

		$modal = ( string) new Element(
			tag        : 'modal',
			attributes : $attributes,
			content    : "<section class='modal-content'>$content</section>", // if array is passed, simple implode
		);

		return new Response(
			content : $modal,
			status  : $status,
			headers : $options + $headers,
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
}