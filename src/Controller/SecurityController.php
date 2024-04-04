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
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;
use Symfony\Component\Security\Http\SecurityRequestAttributes;

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


    public function login(
        CsrfTokenManagerInterface $csrfTokenManager,
    ) : Response {

        return $this->response(
            template   : 'security/login.latte',
            parameters : [
                             'currentUser'  => $this->security->getUser(),
                             'lastUsername' => $this->lastKnownUsername(),
                             'error'        => $this->lastAuthenticationError(),
                             'form'         => [
                                 'csrf_token' => $csrfTokenManager->getToken( 'authenticate' )->getValue(),
                             ],
                         ],
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

    private function lastAuthenticationError( bool $clearSession = true ) : ?AuthenticationException {

        $request                 = $this->request->current;
        $authenticationException = null;

        if ( $request->attributes->has( SecurityRequestAttributes::AUTHENTICATION_ERROR ) ) {
            $authenticationException = $request->attributes->get( SecurityRequestAttributes::AUTHENTICATION_ERROR );
        }
        elseif ( $request->hasSession() && ( $session = $request->getSession() )->has(
                SecurityRequestAttributes::AUTHENTICATION_ERROR,
            ) ) {
            $authenticationException = $session->get( SecurityRequestAttributes::AUTHENTICATION_ERROR );

            if ( $clearSession ) {
                $session->remove( SecurityRequestAttributes::AUTHENTICATION_ERROR );
            }
        }

        return $authenticationException;

    }

    private function lastKnownUsername() : ?string {
        return $this->request->attributes( SecurityRequestAttributes::LAST_USERNAME )
               ?? $this->request->session( SecurityRequestAttributes::LAST_USERNAME );
    }

}