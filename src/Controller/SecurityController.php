<?php

namespace Northrook\Symfony\Core\Controller;

use Northrook\Symfony\Core\Services\CurrentRequestService;
use Northrook\Symfony\Core\Services\SecurityService;
use Northrook\Symfony\Core\Services\SettingsManagementService;
use Northrook\Symfony\Latte\Core as Latte;
use Northrook\Symfony\Latte\Parameters;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

final readonly class SecurityController extends AbstractCoreControllerMethods
{
    public function __construct(
        protected SecurityService                  $security,
        protected CurrentRequestService            $request,
        private readonly SettingsManagementService $settings,
        protected Latte\Environment                $latte,
        protected Parameters\Document              $document,
        protected readonly ?LoggerInterface        $logger,
    ) {
        $this->document->robots = 'noindex, nofollow';
    }


    public function login() : Response {

        return $this->response(
            template : 'security/login.latte',
        );
    }

    public function register() : Response {

        if ( !$this->settings->public( 'registration' ) ) {
            throw new NotFoundHttpException(
                'Public Registration is currently disabled.',
            );
        }

        return new Response(
            content : 'Register Route',
        );
    }

    public function verifyEmail() : JsonResponse {
        return new JsonResponse();
    }
}