<?php

namespace Northrook\Symfony\Core\Controller;

use Northrook\Symfony\Core\Services\CurrentRequestService;
use Northrook\Symfony\Core\Services\FormService;
use Northrook\Symfony\Core\Services\SecurityService;
use Northrook\Symfony\Core\Services\SettingsManagementService;
use Northrook\Symfony\Core\Services\StylesheetGenerationService;
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
    use CoreControllerTrait;

    public function __construct(
        protected SecurityService         $security,
        protected CurrentRequestService   $request,
        private SettingsManagementService $settings,
        protected Latte\Environment       $latte,
        protected Parameters\Document     $document,
        protected ?LoggerInterface        $logger,
    ) {
        $this->document->robots = 'noindex, nofollow';
    }


    public function login(
        CsrfTokenManagerInterface   $csrfTokenManager,
        StylesheetGenerationService $stylesheet,
        FormService                 $form,
    ) : Response {


        $stylesheet->generate();

        $this->document->addStylesheet( 'dir.cache/styles/styles.css' );
        $this->document->addScript( 'dir.root/vendor/northrook/symfony-components-bundle/src/Image/image.js' );
        $this->document->addScript(
            'dir.assets/scripts/core.js',
            'dir.assets/scripts/components.js',
        );

        $this->document->title = 'Northrook';
        $blurb                 = 'Log in to access the admin interface.';


        return $this->response(
            template   : 'security/login.latte',
            parameters : [
                             'seenBefore'   => $this->lastKnownUsername() !== null,
                             'blurb'        => $blurb,
                             'currentUser'  => $this->security->getUser(),
                             'lastUsername' => $this->lastKnownUsername(),
                             'error'        => $this->lastAuthenticationError(),
                             'form'         => [
                                 'template'   => null,
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

    public function verifyEmail( ?string $action ) : JsonResponse {
        return new JsonResponse(
            [ 'action' => $action, ],
        );
    }

    public function resetPassword( ?string $action ) : Response {
        return new JsonResponse(
            [ 'action' => $action, ],
        );
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