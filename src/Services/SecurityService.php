<?php

namespace Northrook\Symfony\Core\Services;

use Symfony\Component\Security\Core\Authentication\Token\Storage\UsageTrackingTokenStorage;
use Symfony\Component\Security\Core\Authorization\AuthorizationChecker;
use Symfony\Component\Security\Core\Authorization\Voter\AuthenticatedVoter;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Csrf\CsrfTokenManager;

final readonly class SecurityService
{
    public const AUTHENTICATED_FULLY      = AuthenticatedVoter::IS_AUTHENTICATED_FULLY;
    public const AUTHENTICATED_REMEMBERED = AuthenticatedVoter::IS_AUTHENTICATED_REMEMBERED;
    public const AUTHENTICATED            = AuthenticatedVoter::IS_AUTHENTICATED;
    public const IMPERSONATOR             = AuthenticatedVoter::IS_IMPERSONATOR;
    public const REMEMBERED               = AuthenticatedVoter::IS_REMEMBERED;
    public const PUBLIC_ACCESS            = AuthenticatedVoter::PUBLIC_ACCESS;

    public function __construct(
        public AuthorizationChecker      $authorization,
        public UsageTrackingTokenStorage $tokenStorage,
        public CsrfTokenManager          $csrf,
    ) {}

    /**
     * Checks if the attribute is granted against the current authentication token and optionally supplied subject.
     *
     * @throws AccessDeniedException
     */
    public function denyAccessUnlessGranted(
        mixed  $attribute = self::AUTHENTICATED_FULLY,
        mixed  $subject = null,
        string $message = 'Access Denied.',
    ) : void {
        if ( !$this->authorization->isGranted( $attribute, $subject ) ) {
            $exception = new AccessDeniedException( $message );
            $exception->setAttributes( [ $attribute ] );
            $exception->setSubject( $subject );

            throw $exception;
        }
    }
}