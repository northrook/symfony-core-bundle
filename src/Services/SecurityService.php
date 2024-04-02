<?php

namespace Northrook\Symfony\Core\Services;

use Symfony\Component\Security\Core\Authentication\Token\Storage\UsageTrackingTokenStorage;
use Symfony\Component\Security\Core\Authorization\AuthorizationChecker;
use Symfony\Component\Security\Csrf\CsrfTokenManager;

final readonly class SecurityService
{

    public function __construct(
        public AuthorizationChecker      $authorization,
        public UsageTrackingTokenStorage $tokenStorage,
        public CsrfTokenManager          $csrf,
    ) {}

}