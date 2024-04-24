<?php

namespace Northrook\Symfony\Core\Controller;

use Northrook\Symfony\Core\File;
use Northrook\Symfony\Core\Services\CurrentRequestService;
use Northrook\Symfony\Core\Services\MailerService;
use Northrook\Symfony\Core\Services\PathfinderService;
use Northrook\Symfony\Core\Services\SecurityService;
use Northrook\Symfony\Core\Services\StylesheetGenerationService;
use Northrook\Symfony\Latte\Core;
use Northrook\Symfony\Latte\Parameters;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Stopwatch\Stopwatch;

final readonly class PublicController
{
    use CoreControllerTrait;

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

        $this->stylesheet->includeStylesheets(
            [
                'dir.core.assets/styles',
            ],
        );
        $path  = File::path( 'dir.cache/styles/styles.css' );
        $saved = $this->stylesheet->save( $path, true );
        $this->document->addStylesheet( 'dir.cache/styles/styles.css' );
        $this->document->addScript(
            'dir.assets/scripts/core.js',
            'dir.assets/scripts/components.js',
        );


    }

    public function index(
        ?string       $route,
        MailerService $mailer,
    ) : Response {
        //
        // if ( time() % 2 === 0 ) {
        //     $this->addFlash( 'info', 'The `time()` is even.', 'Recorded timestamp: `' . time() . '`,' );
        // }
        // $this->addFlash( 'info', 'Info Message', __METHOD__ );
        // $this->addFlash( 'info', 'Info Message' );
        // $this->addFlash( 'error', 'An error occurred!', __METHOD__, );
        // $this->addFlash( 'info', 'Info Message', __METHOD__ );
        // $this->addFlash( 'warning', 'Warning!', 'Nuclear launch detected.' );
        //
        // $this->addFlash( 'error', 'This is an error title', );
        // $this->addFlash( 'info', 'Info Message', __METHOD__ );
        // $this->addFlash( 'notice', 'Notice me', 'Non-hinted type.' );
        //
        //
        // // sleep(1);
        // $this->addFlash( 'info', 'Info Message', __METHOD__ );


        return $this->response(
            template   : 'public.latte',
            parameters : [
                             'route' => $route,
                         ],
        );
    }

}