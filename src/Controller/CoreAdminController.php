<?php

namespace Northrook\Symfony\Core\Controller;

use Northrook\Elements\Render\Template;
use Northrook\Symfony\Core\File;
use Northrook\Symfony\Core\Services\CurrentRequestService;
use Northrook\Symfony\Core\Services\MailerService;
use Northrook\Symfony\Core\Services\PathfinderService;
use Northrook\Symfony\Core\Services\SecurityService;
use Northrook\Symfony\Core\Services\StylesheetGenerationService;
use Northrook\Symfony\Latte\Core;
use Northrook\Symfony\Latte\Parameters;
use Psr\Log\LoggerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authorization\Voter\AuthenticatedVoter;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Stopwatch\Stopwatch;

final readonly class CoreAdminController extends AbstractCoreControllerMethods
{

    public function __construct(
        protected RouterInterface           $router,
        protected HttpKernelInterface       $httpKernel,
        protected ?SerializerInterface      $serializer,
        protected SecurityService           $security,
        protected CurrentRequestService     $request,
        private PathfinderService           $pathfinder,
        private ParameterBagInterface       $parameters,
        private StylesheetGenerationService $stylesheet,
        protected Core\Environment          $latte,
        protected Parameters\Document       $document,
        protected ?LoggerInterface          $logger,
        private ?Stopwatch                  $stopwatch,
    ) {
        $this->security->denyAccessUnlessGranted( AuthenticatedVoter::IS_AUTHENTICATED_FULLY );

        $this->stylesheet->save( File::path( 'dir.assets/build/styles.css' ) );


    }

    public function index(
        MailerService $mailer,
    ) : Response {

        if ( $this->route( 'admin/mailer' ) ) {

            $message = ( new TemplatedEmail() )
                ->to( 'mn@northrook.com' )
                ->subject( 'Hello! Testing from Placeholder' )
                ->htmlTemplate(
                    new Template(
                        <<<HTML
                        <h1>
                            Hello there!
                        </h1>
                        
                        <p>
                            Please confirm your email address by clicking the following link: <br><br>
                            <a href="{signedUrl}">Confirm my Email</a>.
                            This link will expire in {expiresAtMessageKey}.
                        </p>
                        
                        <p>
                            Did the link expire? <a href="#">Request a new link here.</a>.<br>
                            Note that requesting a new link invalidates any previous links.
                        </p>
                        <p>
                            If you did not request this change, please ignore this email.
                        </p>
                        HTML,
                        [
                            'signedUrl'           => 'https://example.com',
                            'expiresAtMessageKey' => '10 minutes',
                        ],


                    ),
                )
            ;

            dump( $message );
            $mail = $mailer->send( $message );
            dd( $mail );
        }

        return $this->response(
            template : 'admin/_admin.latte',
        );
    }

}