<?php

declare( strict_types = 1 );

namespace Northrook\Symfony\Core\Facade;

use LogicException;
use Northrook\Symfony\Core\Autowire\CurrentRequest;
use Northrook\Symfony\Core\DependencyInjection\Facade;
use SensitiveParameter;
use Stringable;
use Symfony\Component\HttpFoundation as Http;
use Symfony\Component\HttpFoundation\Exception\SessionNotFoundException;
use Symfony\Component\HttpFoundation\Session\FlashBagAwareSessionInterface;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Csrf\CsrfToken;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;
use Throwable;

final class Request extends Facade
{
    /**
     * Request represents an HTTP request.
     *
     * @return Http\Request
     */
    public static function current() : Http\Request {
        return Request::getService( CurrentRequest::class )->current;
    }

    /**
     * Request stack that controls the lifecycle of requests.
     *
     * @return Http\RequestStack
     */
    public static function stack() : Http\RequestStack {
        return Request::getService( CurrentRequest::class )->stack;
    }

    /**
     * Retrieve the current active {@see Session}.
     *
     * @return Session
     * @throws SessionNotFoundException if no session is active
     */
    public static function session() : Session {
        return Request::stack()->getSession();
    }

    /**
     * Adds a simple flash message to the current session.
     *
     * @param string                   $type  = ['info', 'success', 'warning', 'error', 'notice'][$any]
     * @param string|Stringable|array  $message
     *
     * @return void
     */
    public static function addFlash( string $type, string | Stringable | array $message ) : void {
        $session = Request::session();

        if ( !$session instanceof FlashBagAwareSessionInterface ) {
            throw new LogicException(
                sprintf(
                    'You cannot use the addFlash method because class "%s" doesn\'t implement "%s".',
                    get_debug_type( $session ), FlashBagAwareSessionInterface::class,
                ),
            );
        }

        $session->getFlashBag()->add( $type, $message );
    }

    /**
     * Checks the validity of a CSRF token.
     *
     * @param string       $id     The id used when generating the token
     * @param string|null  $token  The actual token sent with the request that should be validated
     */
    public static function isCsrfTokenValid(
        string  $id,
        #[SensitiveParameter]
        ?string $token,
    ) : bool {
        return Request::getService( CsrfTokenManagerInterface::class )
                      ->isTokenValid( new CsrfToken( $id, $token ) );
    }

    /**
     * Returns a NotFoundHttpException.
     *
     * This will result in a 404 response code. Usage example:
     *
     * ```
     * throw Request::notFound(`Page not found!`);
     * ```
     *
     * @param string      $message
     * @param ?Throwable  $previous
     *
     * @return NotFoundHttpException
     */
    public static function notFound(
        string     $message = 'Not Found',
        ?Throwable $previous = null,
    ) : NotFoundHttpException {
        return new NotFoundHttpException( $message, $previous );
    }

}