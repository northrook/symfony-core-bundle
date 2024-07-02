<?php

namespace Northrook\Symfony\Core\Component;

use Northrook\Core\Trait\PropertyAccessor;
use Symfony\Component\HttpFoundation as Http;
use Symfony\Component\HttpFoundation\Exception\LogicException;
use Symfony\Component\HttpFoundation\Exception\SessionNotFoundException;
use Symfony\Component\HttpFoundation\HeaderBag;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;
use Symfony\Component\HttpFoundation\Session\FlashBagAwareSessionInterface;

/**
 * @property-read string $routeName
 * @property-read string $routeRoot
 * @property-read string $pathInfo
 * @property-read string $route
 */
final readonly class CurrentRequest
{
    use PropertyAccessor;

    private array $requestType;

    private string $routeName;
    private string $routeRoot;
    private string $pathInfo;
    private string $route;

    public Http\Request $current;

    public function __construct(
        public Http\RequestStack $stack,
    ) {
        $this->current = $this->stack->getCurrentRequest() ?? new Http\Request();
    }

    public function __get( string $property ) {
        return match ( $property ) {
            'routeName' => $this->routeName ??= $this->route(),
            'routeRoot' => $this->routeRoot ??= $this->route( true ),
            'pathInfo'  => $this->pathInfo ??= $this->pathInfo(),
            'route'     => $this->route ??= $this->current->attributes->get( 'route' ) ?? '',
        };
    }

    /**
     * @return object{string: boolean}
     */
    public function type() : object {
        $this->requestType ??= [
            'hypermedia' => $this->headerBag( has : 'hx-request' ),
            'json'       => $this->headerBag( get : 'content-type' ) === 'application/json',
            'html'       => $this->headerBag( get : 'content-type' ) === 'text/html',
        ];
        return (object) $this->requestType;
    }

    /**
     * @param ?string  $get  {@see Http\HeaderBag::get} Returns null if the header is not set
     * @param ?string  $has  {@see Http\HeaderBag::has} Checks if the headerBag contains the header
     *
     * @return null|HeaderBag|string|bool
     */
    public function headerBag( ?string $get = null, ?string $has = null ) : Http\HeaderBag | string | bool | null {

        if ( !$get && !$has ) {
            return $this->current->headers;
        }

        return $get ? $this->current->headers->get( $get ) : $this->current->headers->has( $has );
    }

    /**
     * @param string|null  $get  {@see  SessionInterface::get}
     *
     * @return FlashBagAwareSessionInterface|mixed
     */
    public function session( ?string $get = null ) : mixed {
        try {
            return $get ? $this->current->getSession()->get( $get ) : $this->current->getSession();
        }
        catch ( SessionNotFoundException $exception ) {
            throw new LogicException(
                message  : 'Sessions are disabled. Enable them in "config/packages/framework".',
                previous : $exception,
            );
        }
    }

    /**
     * @param  ?string  $get  {@see Http\Request::get}
     *
     * @return Http\ParameterBag|array|string|int|bool|float|null
     */
    public function parameter( ?string $get = null ) : Http\ParameterBag | array | string | int | bool | float | null {
        return $get ? $this->current->get( $get ) : $this->current->attributes;
    }

    public function attributes( ?string $get = null ) : Http\ParameterBag | array | string | int | bool | float | null {
        return $get ? $this->current->attributes->get( $get ) : $this->current->attributes;
    }

    /**
     * @param string|null  $get  {@see  InputBag::get}
     *
     * @return Http\InputBag|string|int|float|bool|null
     */
    public function query( ?string $get = null ) : Http\InputBag | string | int | float | bool | null {
        return $get ? $this->current->query->get( $get ) : $this->current->query;
    }

    /**
     * @param string|null  $get  {@see Http\InputBag::get}
     *
     * @return Http\InputBag|string|int|float|bool|null
     */
    public function cookies( ?string $get = null ) : Http\InputBag | string | int | float | bool | null {
        return $get ? $this->current->cookies->get( $get ) : $this->current->cookies;
    }

    public function flashBag() : FlashBagInterface {
        return $this->session()->getFlashBag();
    }

    /** Get the current route from the container request stack.
     *
     * @param bool  $root  Return just the root route
     *
     * @return string The current route
     */
    private function route( bool $root = false ) : string {
        $route = $this->current->get( '_route' );

        return ( $root ? strstr( $route, ':', true ) : $route ) ?? '';
    }

    /** Returns the path being requested relative to the executed script.
     *
     * * The path info always starts with a /.
     *
     * @return string The raw path (i.e. not urlecoded)
     */
    private function pathInfo() : string {
        return $this->current->getPathInfo();
    }
}