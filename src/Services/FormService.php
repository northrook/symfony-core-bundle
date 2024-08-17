<?php

namespace Northrook\Symfony\Core\Services;

use Northrook\Symfony\Core\Form\Errors;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;

/**
 * * CSRF
 * * Latte Form
 * * Latte Field validation
 * * Latte Component Errors
 */
class FormService
{

    private string         $id;
    public readonly Errors $errors;


    public function __construct(
        private readonly ParameterBagInterface     $parameter,
        private readonly CsrfTokenManagerInterface $csrfTokenManager,
    ) {
        $this->errors = new Errors();
    }

    public function set( string $id ) : self {
        $this->id = $id;

        return $this;
    }

    public function csrfToken( ?string $tokenId = null ) : string {
        $tokenId ??= $this->id;
        return $this->csrfTokenManager->getToken( $tokenId )->getValue();
    }
}