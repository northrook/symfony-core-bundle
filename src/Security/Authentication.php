<?php

namespace Northrook\Symfony\Core\Security;

// user object access
// authentication
// csrf

use Northrook\Symfony\Core\Facade\Auth;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Authorization\Voter\AuthenticatedVoter;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Csrf\CsrfToken;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;


final readonly class Authentication
{
    public function __construct(
            private AuthorizationCheckerInterface $authorization,
            private TokenStorageInterface         $tokenStorage,
            private CsrfTokenManagerInterface     $tokenManager,
    ) {}

    /**
     * Get a user from the Security Token Storage.
     *
     * @see TokenInterface::getUser()
     */
    public function getUser() : ?UserInterface
    {
        $token = $this->tokenStorage->getToken();

        return $token->getUser();
    }

    /**
     * Checks if the attribute is granted against the current authentication token and optionally supplied subject.
     */
    public function isGranted( mixed $attribute, mixed $subject = null ) : bool
    {
        return $this->authorization->isGranted( $attribute, $subject );
    }

    /**
     * Throws an exception unless the attribute is granted against the current authentication token and optionally
     * supplied subject.
     *
     * @throws AccessDeniedException
     */
    public function denyAccessUnlessGranted(
            mixed  $attribute = AuthenticatedVoter::IS_AUTHENTICATED_FULLY,
            mixed  $subject = null,
            string $message = 'Access Denied',
    ) : void
    {
        if ( !$this->isGranted( $attribute, $subject ) ) {
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
     * @throws \LogicException If the Security component is not available
     */
    public static function accessDenied(
            string      $message = 'Access Denied',
            ?\Throwable $previous = null,
    ) : AccessDeniedException
    {
        return new AccessDeniedException( $message, $previous );
    }

    /**
     * Generate a {@see CsrfToken} for the given tokenId.
     *
     * @param string  $tokenId
     *
     * @return CsrfToken
     */
    public function getToken( string $tokenId ) : CsrfToken
    {
        return $this->tokenManager->getToken( $tokenId );
    }

    /**
     * Checks the validity of a CSRF token.
     *
     * @param string       $id     The id used when generating the token
     * @param string|null  $token  The actual token sent with the request that should be validated
     */
    public function isCsrfTokenValid(
            string  $id,
            #[\SensitiveParameter]
            ?string $token,
    ) : bool
    {
        return $this->tokenManager->isTokenValid(
                new CsrfToken( $id, $token ),
        );
    }
}