<?php


namespace Northrook\Symfony\Core\Controller;

use LogicException;
use Northrook\Symfony\Core\App;
use Northrook\Symfony\Core\Latte\LatteRenderTrait;
use Northrook\Symfony\Core\Services\CurrentRequestService;
use Northrook\Symfony\Latte\Core;
use Northrook\Symfony\Latte\Parameters;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
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
    use LatteRenderTrait;

    private ?Core\Environment       $latteEnvironment = null;
    protected ContainerInterface    $container;
    protected CurrentRequestService $request;
    protected Parameters\Document   $document;
    protected Parameters\Content    $content;

    public function __isset( string $name ) : bool {
        return isset( $this->$name );
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function __get( string $name ) : mixed {
        return match ( $name ) {
            'latte' => $this->getLatteService(),
            default => $this->$name ?? null,
        };
    }

    public function __set( string $name, $value ) : void {
        if ( !App::env( 'public' ) ) {
            throw new LogicException(
                'Dynamic property assignment is not allowed for .' . $this::class . '.',
            );
        }
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
                'core.service.request' => '?' . CurrentRequestService::class,
                'latte.environment'    => '?' . Core\Environment::class,
            ],
        );
    }


    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    protected function getLatteService() : Core\Environment {

        if ( !$this->container->has( 'latte.environment' ) ) {
            throw new LogicException(
                'You cannot use the "latte" or "latteResponse" method if the Latte Bundle is not available.\nTry running "composer require northrook/symfony-latte-bundle".',
            );
        }

        return $this->latteEnvironment ??= $this->container->get( 'latte.environment' );
    }
}