<?php

declare( strict_types = 1 );

namespace Northrook\Symfony\Core\Facade;

use LogicException;
use Northrook\Symfony\Core\DependencyInjection\ServiceContainer;
use Northrook\Symfony\Core\Service\CurrentRequest;
use SensitiveParameter;
use Stringable;
use Symfony\Component\HttpFoundation as Http;
use Symfony\Component\HttpFoundation\Exception\SessionNotFoundException;
use Symfony\Component\HttpFoundation\Session\FlashBagAwareSessionInterface;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Throwable;


final class Request
{
    public static function service() : CurrentRequest
    {
        return ServiceContainer::get( CurrentRequest::class );
    }

    /**
     * Request represents an HTTP request.
     *
     * @return Http\Request
     */
    public static function current() : Http\Request
    {
        return Request::service()->current;
    }

    /**
     * Request stack that controls the lifecycle of requests.
     *
     * @return Http\RequestStack
     */
    public static function stack() : Http\RequestStack
    {
        return Request::service()->stack;
    }

    /**
     * Retrieve the current active {@see Session}.
     *
     * @return Session
     * @throws SessionNotFoundException if no session is active
     */
    public static function session() : Session
    {
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
    public static function addFlash( string $type, string | Stringable | array $message ) : void
    {
        try {
            Request::session()->getFlashBag()->add( $type, $message );
        }
        catch ( SessionNotFoundException $exception ) {
            throw new LogicException(
                    'You cannot use the ' .
                    __METHOD__ . ' method because current session does not implement the '
                    . FlashBagAwareSessionInterface::class . '.',
            );
        }
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
    ) : bool
    {
        trigger_deprecation(
                'northrook/symfony-core-bundle',
                'dev',
                'Use ' . Auth::class . '::isCsrfTokenValid() instead.',
        );
        return Auth::isCsrfTokenValid( $id, $token );
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
    ) : NotFoundHttpException
    {
        return new NotFoundHttpException( $message, $previous );
    }

}