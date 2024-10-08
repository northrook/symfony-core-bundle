<?php

namespace Northrook\Symfony\Core\Controller;

use Northrook\Symfony\Core\DependencyInjection\CoreController;
use Northrook\Symfony\Core\Service\CurrentRequest;
use Northrook\Symfony\Core\Services\FormService;
use Northrook\Symfony\Service\Document\DocumentService;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Profiler\Profiler;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Http\SecurityRequestAttributes;


final class SecurityController extends CoreController
{

    public const string DYNAMIC_TEMPLATE_DIR = 'security';

    public function __construct(
            protected readonly CurrentRequest  $request,
            protected readonly DocumentService $document,
    ) {}

    public function login(
            FormService                 $form,
            StylesheetGenerationService $stylesheet,
            Profiler                    $profiler,
    ) : Response
    {
        $profiler->disable();

        if ( false === $this->request->is( 'hypermedia' ) ) {
            $stylesheet->includeStylesheets( $this::STYLESHEETS )->save( force : true );
        }

        $this->document->stylesheet( 'dir.cache/styles/styles.css' );

        $this->document
                ->script( 'dir.assets/scripts/core.js' )
                ->script( 'dir.assets/scripts/components.js' )
                ->script( 'dir.assets/scripts/navigation.js' )
                ->script( 'dir.assets/scripts/interactions.js' )
                ->script( 'dir.assets/scripts/admin.js' )
        ;

        $this->document->title( 'Northrook' );

        $blurb = 'Log in to access the admin interface.';

        return $this->response(
                content    : 'security/login.latte',
                parameters : [
                                     'seenBefore'   => $this->lastKnownUsername() !== null,
                                     'blurb'        => $blurb,
                                     'currentUser'  => $this->getUser(),
                                     'lastUsername' => $this->lastKnownUsername(),
                                     'error'        => $this->lastAuthenticationError(),
                                     'form'         => [
                                             'template'   => null,
                                             'csrf_token' => $this->getToken( 'authenticate' )?->getValue(),
                                     ],
                             ],
        );
    }

    public function register(
            Profiler $profiler,
    ) : Response
    {
        $profiler->disable();

        if ( !Settings::public( 'security.registration' ) ) {
            throw new NotFoundHttpException(
                    'Public Registration is currently disabled.',
            );
        }

        return new Response(
                content : 'Register Route',
        );
    }

    public function verifyEmail( ?string $action ) : JsonResponse
    {
        return new JsonResponse(
                [ 'action' => $action, ],
        );
    }

    public function resetPassword( ?string $action ) : Response
    {
        return new JsonResponse(
                [ 'action' => $action, ],
        );
    }

    private function lastAuthenticationError( bool $clearSession = true ) : ?AuthenticationException
    {
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

    private function lastKnownUsername() : ?string
    {
        return $this->request->attributes( SecurityRequestAttributes::LAST_USERNAME )
               ?? $this->request->session( SecurityRequestAttributes::LAST_USERNAME );
    }

}