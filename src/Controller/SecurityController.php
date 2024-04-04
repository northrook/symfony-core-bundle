<?php

namespace Northrook\Symfony\Core\Controller;

use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Response;

final readonly class SecurityController
{
    public function __construct(
        private readonly SecurityService  $security,
        private readonly ?LoggerInterface $logger,
    ) {}

    public function verifyEmail() : Response {

        return new Response(
            content : 'OK',
        );
    }
}