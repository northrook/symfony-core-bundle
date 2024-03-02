<?php

namespace Northrook\Symfony\Core\Services;

use LogicException;
use Northrook\Logger\Debug;
use Northrook\Logger\Log;
use Symfony\Component\HttpFoundation\HeaderBag;
use Symfony\Component\HttpFoundation\InputBag;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

/**
 * Current Request Service
 *
 * @property Request $current         Get the current request from the container request stack.
 * @property string $currentRouteName Get the current `root:route` name.
 * @property string $currentRouteRoot Get the current `root` name.
 * @property string $currentPathInfo  Get the current path info. Always starts with a /. Not urlecoded.
 *
 * @author Martin Nielsen <mn@northrook.com>
 * @version ✅ Beta
 */
class CurrentRequestService
{

	/**
	 * @param  string  $name
	 * @return Request|string|null
	 */
	public function __get( string $name ) : Request | string | null {

		$get = match ( $name ) {
			'current'          => $this->currentRequest(),
			'currentRouteName' => $this->currentRoute(),
			'currentRouteRoot' => $this->currentRoute( true ),
			'currentPathInfo'  => $this->currentPathInfo(),
			default            => null,
		};

		if ( null !== $get ) {
			return $get;
		}
		
		$backtrace = Debug::backtrace( 1 );

		return Log::Error(
			"Property {property} does not exist in {service}",
			[
				'property' => $name,
				'service'  => 'CurrentRequestService',
				'caller'   => $backtrace->getCaller(),
				'line'     => $backtrace->getLine(),
			],
		);
	}

	public function __construct(
		private readonly RequestStack $requestStack,
	) {}

	/**
	 * @param  ?string  $get  {@see Request::get}
	 * @return ParameterBag|array|string|int|bool|float|null
	 */
	public function parameter( ?string $get = null ) : ParameterBag | array | string | int | bool | float | null {
		return $get ? $this->currentRequest()->get( $get ) : $this->currentRequest()->attributes;
	}

	/**
	 * @param  string|null  $get  {@see  InputBag::get}
	 * @return InputBag|string|int|float|bool|null
	 */
	public function query( ?string $get = null ) : InputBag | string | int | float | bool | null {
		return $get ? $this->currentRequest()->query->get( $get ) : $this->currentRequest()->query;
	}

	/**
	 * @param  string|null  $get  {@see  SessionInterface::get}
	 * @return SessionInterface|mixed
	 */
	public function session( ?string $get = null ) : mixed {
		if ( false === $this->currentRequest()->hasSession() ) {
			return null;
		}
		return $get ? $this->currentRequest()->getSession()->get( $get ) : $this->currentRequest()->getSession();
	}

	/**
	 * @param  string|null  $get  {@see HeaderBag::get}
	 * @return HeaderBag|string|null
	 */
	public function headers( ?string $get = null ) : HeaderBag | string | null {
		return $get ? $this->currentRequest()->headers->get( $get ) : $this->currentRequest()->headers;
	}

	/**
	 * @param  string|null  $get  {@see InputBag::get}
	 * @return InputBag|string|int|float|bool|null
	 */
	public function cookies( ?string $get = null ) : InputBag | string | int | float | bool | null {
		return $get ? $this->currentRequest()->cookies->get( $get ) : $this->currentRequest()->cookies;
	}

	/** Get the current route from the container request stack.
	 *
	 * @param  bool  $root  Return just the root route
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
	 * @return Request The current request
	 * @version 1.0 ✅
	 * @uses    \Symfony\Component\HttpFoundation\RequestStack
	 */
	private function currentRequest() : Request {
		$request = $this->requestStack->getCurrentRequest();

		if ( null !== $request ) {
			Log::Warning(
				'Could not find the current request. Returned new {return}.',
				[
					'return' => 'Request()',
					'class'  => Request::class,
					'caller' => debug_backtrace( DEBUG_BACKTRACE_IGNORE_ARGS )[ 0 ],
				],
			);
		}

		return $request ?? new Request();
	}
}