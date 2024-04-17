<?php

namespace Northrook\Symfony\Core\Controller;

use Northrook\Symfony\Core\Components\Notification;
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

final readonly class PublicController extends AbstractCoreControllerMethods
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
        $this->addFlash(

            'notice',
            new Notification(
                Notification::ERROR,
                'Welcome to Northrook Symfony',
                'Long Message',
            ),
        );


        return $this->response(
            template   : 'public.latte',
            parameters : [
                             'route' => $route,
                         ],
        );
    }

}