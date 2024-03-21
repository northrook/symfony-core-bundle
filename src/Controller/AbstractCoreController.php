<?php /** @noinspection PhpUnused */

namespace Northrook\Symfony\Core\Controller;

use LogicException;
use Northrook\Elements\Element;
use Northrook\Logger\Log;
use Northrook\Symfony\Core\Components\LatteComponentPreprocessor;
use Northrook\Symfony\Core\Services\CurrentRequestService;
use Northrook\Symfony\Latte\Core;
use Northrook\Symfony\Latte\Parameters;
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
 * * Integrates {@see Core\Environment} from `northrook/symfony-latte-bundle`
 *
 * @property ?Core\Environment $latte
 *
 * @version 0.1.0 ☑️
 * @author  Martin Nielsen <mn@northrook.com>
 */
abstract class AbstractCoreController extends AbstractController
{
    private ?Core\Environment       $latteEnvironment = null;
    protected ContainerInterface    $container;
    protected CurrentRequestService $request;
    protected Parameters\Document   $document;
    protected Parameters\Content    $content;

    public function __get( string $name ) : mixed {
        $name = "get" . ucfirst( $name ) . 'Service';
        if ( method_exists( $this, $name ) ) {
            try {
                return $this->$name() ?? null;
            }
            catch ( ContainerExceptionInterface | NotFoundExceptionInterface$e ) {
                Log::error( $e->getMessage() );
            }
        }
        return null;
    }

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
                'core.service.request'    => '?' . CurrentRequestService::class,
                'core.latte.preprocessor' => '?' . LatteComponentPreprocessor::class,
                'latte.environment'       => '?' . Core\Environment::class,
            ],
        );
    }

    /**
     * Run at the very start of the {@see Core\Environment} render chain.
     *
     * @return void
     */
    protected function __onLatteRender() : void {}

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    protected function getLatteService() : Core\Environment {

        if ( !$this->container->has( 'latte.environment' ) || !$this->container->has( 'core.latte.preprocessor' ) ) {
            throw new LogicException(
                'You cannot use the "latte" or "latteResponse" method if the Latte Bundle is not available.\nTry running "composer require northrook/symfony-latte-bundle".'
            );
        }

        $this->latteEnvironment ??= $this->container->get( 'latte.environment' );

        // $this->latteEnvironment->addExtension();

        $this->latteEnvironment->addPreprocessor( $this->container->get( 'core.latte.preprocessor' ) );

        return $this->latteEnvironment;
    }

    /**
     * @param string             $view  Template file or template string
     * @param object|array|null  $parameters
     *
     * @return string
     */
    protected function latte(
        string                $view,
        object | array | null $parameters = null,
    ) : string {

//        $this->latteEnviroment ??= $this->getLatte();

        $this->__onLatteRender();

        $parameters ??= [];

//        dd( property_exists( $this, 'document' ), $this->document::class, DocumentParameters::class );

        if ( is_array( $parameters ) && isset( $this->document ) ) {
            $parameters[ 'document' ] = $this->document;
//            dd( $parameters );
        }


        return $this->latte->render(
            template   : $view,
            parameters : $parameters,
        );
    }

    /**
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
     * @param string        $view
     * @param object|array  $parameters
     * @param int           $status
     * @param array         $headers
     *
     * @return Response
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