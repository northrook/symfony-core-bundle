<?php

declare( strict_types = 1 );

namespace Northrook\Symfony\Core\Facade;

use LogicException;
use Northrook\Symfony\Core\DependencyInjection\Facade;
use SensitiveParameter;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Authorization\Voter\AuthenticatedVoter;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Csrf\CsrfToken;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;
use Throwable;

final class Auth extends Facade
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
     * Generate a {@see CsrfToken} for the given tokenId.
     *
     * @param string  $tokenId
     *
     * @return CsrfToken
     */
    public static function getToken( string $tokenId ) : CsrfToken {
        return Auth::getService( CsrfTokenManagerInterface::class )->getToken( $tokenId );
    }

    /**
     * Checks if the attribute is granted against the current authentication token and optionally supplied subject.
     *
     */
    public static function isGranted( mixed $attribute, mixed $subject = null ) : bool {
        return Auth::getService( AuthorizationCheckerInterface::class )->isGranted( $attribute, $subject );
    }

    /**
     * Throws an exception unless the attribute is granted against the current authentication token and optionally
     * supplied subject.
     *
     * @throws AccessDeniedException
     */
    public static function denyAccessUnlessGranted(
        mixed  $attribute = AuthenticatedVoter::IS_AUTHENTICATED_FULLY,
        mixed  $subject = null,
        string $message = 'Access Denied',
    ) : void {

        if ( !Auth::isGranted( $attribute, $subject ) ) {
            $exception = Auth::accessDenied( $message );
            $exception->setAttributes( [ $attribute ] );
            $exception->setSubject( $subject );

            throw $exception;
        }
    }

    /**
     * Returns an AccessDeniedException.
     *
     * This will result in a 403 response code. Usage example:
     *
     *  ```
     * throw Auth::accessDenied('Unable to access this page!');
     *  ```
     *
     * @throws LogicException If the Security component is not available
     */
    public static function accessDenied(
        string     $message = 'Access Denied',
        ?Throwable $previous = null,
    ) : AccessDeniedException {
        return new AccessDeniedException( $message, $previous );
    }
}