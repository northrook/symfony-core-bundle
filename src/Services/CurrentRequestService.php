<?php

namespace Northrook\Symfony\Core\Services;

use LogicException;
use Northrook\Logger\Log\Level;
use Northrook\Logger\Log\Timestamp;
use Northrook\Symfony\Core as Core;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation as Http;
use Symfony\Component\HttpFoundation\Exception\SessionNotFoundException;
use Symfony\Component\HttpFoundation\HeaderBag;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;
use Symfony\Component\HttpFoundation\Session\FlashBagAwareSessionInterface;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

/**
 * Current Request Service
 *
 * @author Martin Nielsen <mn@northrook.com>
 */
readonly class CurrentRequestService
{

    public string       $route;
    public string       $routeName;
    public string       $routeRoot;
    public string       $pathInfo;
    public Http\Request $current;


    /**
     * Injected into {@see Core\Controller\CoreController} as `$request`, and available via {@see Core\Request} om-demand.
     *
     * @param Http\RequestStack     $requestStack
     * @param null|LoggerInterface  $logger
     */
    public function __construct(
        private Http\RequestStack $requestStack,
        private ?LoggerInterface  $logger = null,
    ) {
        $this->current   = $this->currentRequest();
        $this->routeName = $this->currentRoute();
        $this->routeRoot = $this->currentRoute( true );
        $this->pathInfo  = $this->currentPathInfo();
        $this->route     = $this->current->attributes->get( 'route' ) ?? '';
    }


    /**
     * @param string  $type  = ['hypermedia', 'json'][$any]
     *
     * @return bool
     */
    final public function is( string $type ) : bool {
        return match ( $type ) {
            'hypermedia' => $this->headerBag( has : 'hx-request' ),
            'json'       => $this->headerBag( get : 'content-type' ) === 'application/json',
            default      => false,
        };
    }

    final public function route( ?string $is = null ) : string | bool {
        return $is === null ? $this->route : $this->route === $is;
    }

    /**
     * @param  ?string  $get  {@see Http\Request::get}
     *
     * @return Http\ParameterBag|array|string|int|bool|float|null
     */
    public function parameter( ?string $get = null ) : Http\ParameterBag | array | string | int | bool | float | null {
        return $get ? $this->currentRequest()->get( $get ) : $this->currentRequest()->attributes;
    }

    public function attributes( ?string $get = null ) : Http\ParameterBag | array | string | int | bool | float | null {
        return $get ? $this->currentRequest()->attributes->get( $get ) : $this->currentRequest()->attributes;
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
     * @param string | Level  $type  = ['error', 'warning', 'info', 'success'][$any]
     * @param string          $message
     * @param null|string     $description
     * @param null|int        $timeoutMs
     * @param bool            $log
     *
     * @return void
     */
    public function addFlash(
        string | Level $type,
        string         $message,
        ?string        $description = null,
        ?int           $timeoutMs = null,
        bool           $log = false,
    ) : void {

        if ( $type instanceof Level ) {
            $level = $type->name;
        }
        else {
            $level = in_array( ucfirst( $type ), Level::NAMES ) ? ucfirst( $type ) : 'Info';
        }


        if ( $log ) {
            $this?->logger->log( $level, $message );
        }

        $this->flashBag()->add(
            $type,
            [
                'level'       => $level,
                'message'     => $message,
                'description' => $description,
                'timeout'     => $timeoutMs,
                'timestamp'   => new Timestamp(),
            ],
        );
    }

    public function flashBag() : FlashBagInterface {
        return $this->session()->getFlashBag();
    }

    /**
     * @param string|null  $get  {@see  SessionInterface::get}
     *
     * @return FlashBagAwareSessionInterface|mixed
     */
    public function session( ?string $get = null ) : mixed {
        try {
            return $get ? $this->currentRequest()->getSession()->get( $get ) : $this->currentRequest()->getSession();
        }
        catch ( SessionNotFoundException $exception ) {
            throw new LogicException(
                message  : 'Sessions are disabled. Enable them in "config/packages/framework".',
                previous : $exception,
            );
        }
    }

    /**
     * @param ?string  $get  {@see Http\HeaderBag::get} Returns null if the header is not set
     * @param ?string  $has  {@see Http\HeaderBag::has} Checks if the headerBag contains the header
     *
     * @return null|HeaderBag|string|bool
     */
    final public function headerBag( ?string $get = null, ?string $has = null,
    ) : Http\HeaderBag | string | bool | null {

        if ( !$get && !$has ) {
            return $this->current->headers;
        }

        return $get ? $this->current->headers->get( $get ) : $this->current->headers->has( $has );
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