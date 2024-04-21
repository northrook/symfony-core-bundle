<?php

namespace Northrook\Symfony\Core\Services;

use LogicException;
use Northrook\Core\Debug\Backtrace;
use Northrook\Logger\Log\Level;
use Northrook\Logger\Log\Timestamp;
use Northrook\Symfony\Core as Core;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation as Http;
use Symfony\Component\HttpFoundation\Exception\SessionNotFoundException;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;
use Symfony\Component\HttpFoundation\Session\FlashBagAwareSessionInterface;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

/**
 * Current Request Service
 *
 * @property Http\Request $current   Get the current request from the container request stack.
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
            'current'   => $this->currentRequest(),
            'routeName' => $this->currentRoute(),
            'routeRoot' => $this->currentRoute( true ),
            'pathInfo'  => $this->currentPathInfo(),
            default     => null,
        };

        if ( null !== $get ) {
            return $get;
        }

        $backtrace = Backtrace::get( 1 );

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
     * `Not supported.`
     *
     * @param string  $name
     * @param mixed   $value
     *
     * @return void
     */
    public function __set( string $name, mixed $value ) : void {
        trigger_error( CurrentRequestService::class . '::__set() is not supported.', E_USER_NOTICE );
    }

    /**
     * Check if a given property is set.
     *
     * @param string  $name
     *
     * @return bool
     */
    public function __isset( string $name ) : bool {
        return isset( $this->$name );
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