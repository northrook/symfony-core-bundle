<?php

namespace Northrook\Symfony\Core\DependencyInjection\Trait;


use LogicException;
use Northrook\Symfony\Core\DependencyInjection\CoreDependencies;
use SensitiveParameter;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Csrf\CsrfToken;
use Throwable;

trait SecurityServices
{
    protected readonly CoreDependencies $get;

    /**
     * Retrieve the current user from {@see $tokenStorage}.
     *
     * Returns {@see null} if the no user is authenticated.
     *
     * @return null|UserInterface
     */
    final protected function getUser() : ?UserInterface {
        return $this->get->tokenStorage->getToken() ? $this->get->tokenStorage->getToken()->getUser() : null;
    }

    final protected function getToken( string $tokenId = self::class ) : CsrfToken {
        return $this->get->csrf->getToken( $tokenId );
    }

    /**
     * Checks the validity of a CSRF token.
     *
     * @param string       $id     The id used when generating the token
     * @param string|null  $token  The actual token sent with the request that should be validated
     */
    protected function isCsrfTokenValid(
        string $id, #[SensitiveParameter]
    ?string    $token,
    ) : bool {
        return $this->get->csrf->isTokenValid( new CsrfToken( $id, $token ) );
    }

    /**
     * Checks if the attribute is granted against the current authentication token and optionally supplied subject.
     *
     */
    final protected function isGranted( mixed $attribute, mixed $subject = null ) : bool {
        return $this->get->authorization->isGranted( $attribute, $subject );
    }

    /**
     * Throws an exception unless the attribute is granted against the current authentication token and optionally
     * supplied subject.
     *
     * @throws AccessDeniedException
     */
    final protected function denyAccessUnlessGranted(
        mixed $attribute, mixed $subject = null, string $message = 'Access Denied.',
    ) : void {
        if ( !$this->isGranted( $attribute, $subject ) ) {
            $exception = $this->createAccessDeniedException( $message );
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
     *     throw $this->createAccessDeniedException('Unable to access this page!');
     *
     * @throws LogicException If the Security component is not available
     */
    final protected function createAccessDeniedException(
        string     $message = 'Access Denied.',
        ?Throwable $previous = null,
    ) : AccessDeniedException {
        return new AccessDeniedException( $message, $previous );
    }
}