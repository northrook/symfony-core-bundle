<?php

namespace Northrook\Symfony\Core\Services;

use Northrook\Logger\Debug;
use Northrook\Symfony\Core as Core;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation as Http;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

/**
 * Current Request Service
 *
 * @property Http\Request $request   Get the current request from the container request stack.
 * @property string       $routeName Get the current `root:route` name.
 * @property string       $routeRoot Get the current `root` name.
 * @property string       $pathInfo  Get the current path info. Always starts with a /. Not urlecoded.
 *
 * @author Martin Nielsen <mn@northrook.com>
 */
class CurrentRequestService
{
    /**
     * Injected into {@see Core\Controller\AbstractCoreController} as $current, and available via {@see Core\Request} om-demand.
     *
     * @param Http\RequestStack     $requestStack
     * @param null|LoggerInterface  $logger
     */
    public function __construct(
        private readonly Http\RequestStack $requestStack,
        private readonly ?LoggerInterface  $logger = null,
    ) {}

    /**
     * @param string  $name
     *
     * @return Http\Request|string|null
     */
    public function __get( string $name ) : Http\Request | string | null {

        $get = match ( $name ) {
            'request'   => $this->currentRequest(),
            'routeName' => $this->currentRoute(),
            'routeRoot' => $this->currentRoute( true ),
            'pathInfo'  => $this->currentPathInfo(),
            default     => null,
        };

        if ( null !== $get ) {
            return $get;
        }

        $backtrace = Debug::backtrace( 1 );

        $this->logger?->warning(
            'Could not find the current request. Returned new {return}.',
            [
                'property' => $name,
                'service'  => 'CurrentRequestService',
                'caller'   => $backtrace->getCaller(),
                'line'     => $backtrace->getLine(),
            ],
        );

        return null;
    }

    /**
     * @param  ?string  $get  {@see Http\Request::get}
     *
     * @return Http\ParameterBag|array|string|int|bool|float|null
     */
    public function parameter( ?string $get = null ) : Http\ParameterBag | array | string | int | bool | float | null {
        return $get ? $this->currentRequest()->get( $get ) : $this->currentRequest()->attributes;
    }

    /**
     * @param string|null  $get  {@see  InputBag::get}
     *
     * @return Http\InputBag|string|int|float|bool|null
     */
    public function query( ?string $get = null ) : Http\InputBag | string | int | float | bool | null {
        return $get ? $this->currentRequest()->query->get( $get ) : $this->currentRequest()->query;
    }

    /**
     * @param string|null  $get  {@see  SessionInterface::get}
     *
     * @return SessionInterface|mixed
     */
    public function session( ?string $get = null ) : mixed {
        if ( false === $this->currentRequest()->hasSession() ) {
            return null;
        }
        return $get ? $this->currentRequest()->getSession()->get( $get ) : $this->currentRequest()->getSession();
    }

    /**
     * @param string|null  $get  {@see Http\HeaderBag::get}
     *
     * @return Http\HeaderBag|string|null
     */
    public function headers( ?string $get = null ) : Http\HeaderBag | string | null {
        return $get ? $this->currentRequest()->headers->get( $get ) : $this->currentRequest()->headers;
    }

    /**
     * @param string|null  $get  {@see Http\InputBag::get}
     *
     * @return Http\InputBag|string|int|float|bool|null
     */
    public function cookies( ?string $get = null ) : Http\InputBag | string | int | float | bool | null {
        return $get ? $this->currentRequest()->cookies->get( $get ) : $this->currentRequest()->cookies;
    }

    /** Get the current route from the container request stack.
     *
     * @param bool  $root  Return just the root route
     *
     * @return ?string The current route
     */
    private function currentRoute( bool $root = false ) : ?string {
        $route = $this->currentRequest()->get( '_route' );

        return $root ? strstr( $route, ':', true ) : $route;
    }

    /** Returns the path being requested relative to the executed script.
     *
     * * The path info always starts with a /.
     *
     * @return string The raw path (i.e. not urlecoded)
     */
    private function currentPathInfo() : string {
        return $this->currentRequest()->getPathInfo();
    }

    /** Get the current request from the container request stack.
     *
     * * Will return the current request if it exists, otherwise it will return a new request.
     *
     * @return Http\Request The current request
     * @version 1.0 ✅
     * @uses    Http\RequestStack
     */
    private function currentRequest() : Http\Request {
        $request = $this->requestStack->getCurrentRequest();

        if ( null !== $request && $this->logger ) {
            $this->logger->warning(
                'Could not find the current request. Returned new {return}.',
                [
                    'return' => 'Request()',
                    'class'  => Http\Request::class,
                    'caller' => debug_backtrace( DEBUG_BACKTRACE_IGNORE_ARGS )[ 0 ],
                ],
            );
        }

        return $request ?? new Http\Request();
    }
}