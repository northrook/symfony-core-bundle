<?php

namespace Northrook\Symfony\Core\Services;

use Symfony\Component\HttpKernel\HttpKernel;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\Routing\Router;
use Symfony\Component\Routing\RouterInterface;

/**
 * @property Router     $router
 * @property HttpKernel $kernel
 */
final readonly class HttpService
{
    public function __construct(
        private RouterInterface     $routerInterface,
        private HttpKernelInterface $kernelInterface,
    ) {}

    public function __get( string $name ) : mixed {
        return match ( $name ) {
            'router' => $this->routerInterface,
            'kernel' => $this->kernelInterface,
            default  => null,
        };
    }

    public function __set( string $name, mixed $value ) : void {}

    public function __isset( string $name ) : bool {
        return property_exists( $this, $name );
    }
}