<?php

namespace Northrook\Symfony\Core\Controller;

use Northrook\Symfony\Core\DependencyInjection\CoreController;
use Northrook\Symfony\Core\Facade\Path;
use Northrook\Symfony\Core\Services\CurrentRequestService;
use Northrook\Symfony\Core\Services\DocumentService;
use Northrook\Symfony\Core\Services\StylesheetGenerationService;
use Symfony\Component\HttpFoundation\Response;

final class PublicController extends CoreController
{
    public const STYLESHEETS          = [ 'dir.core.assets/styles' ];
    public const DYNAMIC_TEMPLATE_DIR = 'public';

    public function __construct(
        protected readonly CurrentRequestService       $request,
        protected readonly DocumentService             $document,
        protected readonly StylesheetGenerationService $stylesheet,
    ) {
        if ( false === $this->request->is( 'hypermedia' ) ) {
            $this->stylesheet->includeStylesheets( $this::STYLESHEETS )->save( force : true );
        }

        $this->document->stylesheet( 'dir.cache/styles/styles.css' );

        $this->document->script( 'dir.assets/scripts/core.js' );

        $this->document->body();
    }

    public function index(
        ?string $route,
    ) : Response {

        print_r( Path::getParameter( 'path.favicon' ) );

        return $this->response(
            template   : 'public.latte',
            parameters : [ 'route' => $route ],
        );
    }

}