<?php

namespace Northrook\Symfony\Core\Controller;

use Northrook\Symfony\Core\Services\CurrentRequestService;
use Northrook\Symfony\Core\Services\PathfinderService;
use Psr\Container\ContainerInterface;
use Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException;
use Symfony\Component\DependencyInjection\ParameterBag\ContainerBagInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;
use Symfony\Contracts\Service\Attribute\Required;
use Symfony\Contracts\Service\ServiceSubscriberInterface;

abstract class CoreController implements ServiceSubscriberInterface
{
    protected ContainerInterface $container;

    #[Required]
    public function setContainer( ContainerInterface $container ) : ?ContainerInterface {
        $previous        = $this->container ?? null;
        $this->container = $container;
        return $previous;
    }

    /**
     * Gets a container parameter by its name.
     */
    protected function getParameter( string $name ) : array | bool | string | int | float | \UnitEnum | null {
        if ( !$this->container->has( 'parameter_bag' ) ) {
            throw new ServiceNotFoundException(
                'parameter_bag.', null, null, [], sprintf(
                'The "%s::getParameter()" method is missing a parameter bag to work properly. Did you forget to register your controller as a service subscriber? This can be fixed either by using autoconfiguration or by manually wiring a "parameter_bag" in the service locator passed to the controller.',
                static::class,
            ),
            );
        }

        return $this->container->get( 'parameter_bag' )->get( $name );
    }

    public static function getSubscribedServices() : array {
        return [
            'router'                         => '?' . RouterInterface::class,
            'core.service.request'           => '?' . CurrentRequestService::class,
            'core.service.pathfinder'        => '?' . PathfinderService::class,
            'request_stack'                  => '?' . RequestStack::class,
            'http_kernel'                    => '?' . HttpKernelInterface::class,
            'security.authorization_checker' => '?' . AuthorizationCheckerInterface::class,
            'form.factory'                   => '?' . FormFactoryInterface::class,
            'security.token_storage'         => '?' . TokenStorageInterface::class,
            'security.csrf.token_manager'    => '?' . CsrfTokenManagerInterface::class,
            'parameter_bag'                  => '?' . ContainerBagInterface::class,
        ];
    }
}