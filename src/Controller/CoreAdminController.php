<?php

namespace Northrook\Symfony\Core\Controller;

use Northrook\Logger\Status\HTTP;
use Northrook\Symfony\Core\File;
use Northrook\Symfony\Core\Services\CurrentRequestService;
use Northrook\Symfony\Core\Services\PathfinderService;
use Northrook\Symfony\Core\Services\StylesheetGenerationService;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\Response;

final readonly class CoreAdminController
{
    public function __construct(
        private CurrentRequestService       $request,
        private PathfinderService           $pathfinder,
        private ParameterBagInterface       $parameters,
        private StylesheetGenerationService $stylesheet,
        private ?LoggerInterface            $logger,
    ) {
        $this->stylesheet->save( File::path( 'dir.assets/build/styles.css' ) );
    }

    public function index() : Response {

        dd(
            $this,
        );
        
        return new Response(
            content : 'This is the admin index page',
            status  : HTTP::OK,
        );
    }

}