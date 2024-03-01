<?php

namespace Northrook\Symfony\Core\Services;

use Symfony\Component\HttpFoundation\HeaderBag;
use Symfony\Component\HttpFoundation\InputBag;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class CurrentRequestService
{

	public function __construct(
		private readonly RequestStack $requestStack,
	) {}


	/**
	 * @param  ?string  $get  {@see Request::get}
	 * @return ParameterBag|array|string|int|bool|float|null
	 */
	public function parameter( ?string $get = null ) : ParameterBag | array | string | int | bool | float | null {
		return $get ? $this->getRequest()->get( $get ) : $this->getRequest()->attributes;
	}

	/**
	 * @param  string|null  $get  {@see  InputBag::get}
	 * @return InputBag|string|int|bool|float|null
	 */
	public function query( ?string $get = null ) : InputBag | string | int | bool | null | float {
		return $get ? $this->getRequest()->query->get( $get ) : $this->getRequest()->query;
	}

	/**
	 * @param  string|null  $get  {@see  SessionInterface::get}
	 * @return SessionInterface|mixed
	 */
	public function session( ?string $get = null ) : mixed {
		if ( false === $this->getRequest()->hasSession() ) {
			return null;
		}
		return $get ? $this->getRequest()->getSession()->get( $get ) : $this->getRequest()->getSession();
	}


	/**
	 * @param  string|null  $get  {@see HeaderBag::get}
	 * @return HeaderBag|string|null
	 */
	public function headers( ?string $get = null ) : HeaderBag | string | null {
		return $get ? $this->getRequest()->headers->get( $get ) : $this->getRequest()->headers;
	}

	/**
	 * @param  string|null  $get  {@see InputBag::get}
	 * @return InputBag|string|null
	 */
	public function cookies( ?string $get = null ) : InputBag | string | null {
		return $get ? $this->getRequest()->cookies->get( $get ) : $this->getRequest()->cookies;
	}

	/** Get the current route from the container request stack.
	 *
	 * @param  bool  $root  Return just the root route
	 * @return ?string The current route
	 */
	public function currentRoute( bool $root = false ) : ?string {
		$route = $this->getRequest()->get( '_route' );

		return $root ? strstr( $route, ':', true ) : $route;
	}

	/** Returns the path being requested relative to the executed script.
	 *
	 * * The path info always starts with a /.
	 *
	 * @return string The raw path (i.e. not urlecoded)
	 */
	public function currentPathInfo() : string {
		return $this->getRequest()->getPathInfo();
	}

	public function getRequest() : Request {
		return $this->requestStack->getCurrentRequest();
	}

}