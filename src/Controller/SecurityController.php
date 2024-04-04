<?php

namespace Northrook\Symfony\Core\Controller;

use Northrook\Symfony\Core\Services\SecurityService;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

final readonly class SecurityController
{
    public function __construct(
        private readonly SecurityService  $security,
        private readonly ?LoggerInterface $logger,
    ) {}


    public function login() : Response {

        return new Response(
            content : 'Login Route',
        );
    }

    public function verifyEmail() : Response {

        if ( !$this->security->getUser() ) {
            throw new NotFoundHttpException();
        }

        return new Response(
            content : 'OK',
        );
    }
}