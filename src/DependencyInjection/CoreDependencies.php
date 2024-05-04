<?php

namespace Northrook\Symfony\Core\DependencyInjection;

use Closure;
use Northrook\Core\Service\ServiceResolver;
use Northrook\Core\Service\ServiceResolverTrait;
use Northrook\Symfony\Core\Services\CurrentRequestService;
use Northrook\Symfony\Core\Services\DocumentService;
use Northrook\Symfony\Latte\Core\Environment;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\UsageTrackingTokenStorage;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Stopwatch\Stopwatch;

/**
 * @property RouterInterface               $router
 * @property HttpKernelInterface           $httpKernel
 * @property ParameterBagInterface         $parameterBag
 * @property CurrentRequestService         $request
 * @property SerializerInterface           $serializer
 * @property AuthorizationCheckerInterface $authorization
 * @property UsageTrackingTokenStorage     $tokenStorage
 * @property CsrfTokenManagerInterface     $csrf
 * @property Environment                   $latte
 * @property DocumentService               $document
 * @property ?LoggerInterface              $logger
 * @property ?Stopwatch                    $stopwatch
 */
final class CoreDependencies extends ServiceResolver
{
    use ServiceResolverTrait;

    public function __construct(
        RouterInterface | Closure               $router,
        HttpKernelInterface | Closure           $httpKernel,
        ParameterBagInterface | Closure         $parameterBag,
        CurrentRequestService | Closure         $request,
        SerializerInterface | Closure           $serializer,
        AuthorizationCheckerInterface | Closure $authorization,
        UsageTrackingTokenStorage | Closure     $tokenStorage,
        CsrfTokenManagerInterface | Closure     $csrf,
        Environment | Closure                   $latte,
        DocumentService | Closure               $document,
        null | LoggerInterface | Closure        $logger,
        null | Stopwatch | Closure              $stopwatch,
    ) {
        $this->setMappedService( get_defined_vars() );
    }


}