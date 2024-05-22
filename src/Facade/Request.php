<?php

namespace Northrook\Symfony\Core\Facade;

use Northrook\Symfony\Core\DependencyInjection\Facade;
use SensitiveParameter;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Csrf\CsrfToken;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;
use Throwable;

final class Request extends Facade
{

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
     * throw Request::notFound('Page not found!');
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