<?php

namespace Northrook\Symfony\Core\Controller;

use Northrook\Logger\Status\HTTP;
use Northrook\Symfony\Core\File;
use Northrook\Symfony\Core\Services\CurrentRequestService;
use Northrook\Symfony\Core\Services\PathfinderService;
use Northrook\Symfony\Core\Services\StylesheetGenerationService;
use Northrook\Symfony\Latte\Core;
use Northrook\Symfony\Latte\Parameters;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Stopwatch\Stopwatch;

final readonly class CoreAdminController
{
    public function __construct(
        private CurrentRequestService       $request,
        private PathfinderService           $pathfinder,
        private ParameterBagInterface       $parameters,
        private StylesheetGenerationService $stylesheet,
        private Core\Environment            $latte,
        private Parameters\Document         $document,
        private ?LoggerInterface            $logger,
        private ?Stopwatch                  $stopwatch,
    ) {
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