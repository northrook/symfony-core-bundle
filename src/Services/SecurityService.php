<?php

namespace Northrook\Symfony\Core\Services;

use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationChecker;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Csrf\CsrfTokenManager;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;

/**
 * @property AuthorizationChecker $authorization
 * @property TokenStorage         $tokenStorage
 * @property CsrfTokenManager     $csrf
 */
final readonly class SecurityService
{

    public function __construct(
        private AuthorizationCheckerInterface $authorizationInterface,
        private TokenStorageInterface         $tokenStorageInterface,
        private CsrfTokenManagerInterface     $csrfInterface,
    ) {}

    public function __get( string $name ) : TokenStorage | AuthorizationChecker | CsrfTokenManager {
        return match ( $name ) {
            'authorization' => $this->authorizationInterface,
            'tokenStorage'  => $this->tokenStorageInterface,
            'csrf'          => $this->csrfInterface,
        };
    }

    public function __set( string $name, mixed $value ) : void {
        return;
    }

    public function __isset( string $name ) : bool {
        return property_exists( $this, $name );
    }
}