<?php

namespace Northrook\Symfony\Core\DependencyInjection;

use Psr\Log\LoggerInterface;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Stopwatch\Stopwatch;

/**
 * @property RouterInterface                $router
 * @property HttpKernelInterface            $httpKernel
 * @property ?SerializerInterface           $serializer
 * @property ?AuthorizationCheckerInterface $authorization
 * @property ?LoggerInterface               $logger
 * @property ?Stopwatch                     $stopwatch
 */
final class ControllerDependencies extends LazyDependencies
{
    public function __construct(
        RouterInterface | \Closure               $router,
        HttpKernelInterface | \Closure           $httpKernel,
        SerializerInterface | \Closure           $serializer,
        AuthorizationCheckerInterface | \Closure $authorization,
        null | LoggerInterface | \Closure        $logger,
        null | Stopwatch | \Closure              $stopwatch,
    ) {
        $this->setMappedService( get_defined_vars() );
    }

}