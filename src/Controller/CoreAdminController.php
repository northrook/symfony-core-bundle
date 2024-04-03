<?php

namespace Northrook\Symfony\Core\Controller;

use Northrook\Logger\Status\HTTP;
use Northrook\Symfony\Core\File;
use Northrook\Symfony\Core\Services\CurrentRequestService;
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

    public function index() : Response {
        return $this->response(
            template : 'admin/_admin.latte',
        );
    }

    private function response(
        string         $template,
        object | array $parameters = [],
        int | HTTP     $status = HTTP::OK,
    ) : Response {

        if ( is_array( $parameters ) && isset( $this->document ) ) {
            $parameters[ 'document' ] = $this->document;
        }

        return new Response(
            content : $this->render( $template, $parameters ),
            status  : $status,
        );
    }

    private function render(
        string         $template,
        object | array $parameters = [],
    ) : string {

        return $this->latte->render(
            template   : $template,
            parameters : $parameters,
        );
    }

}