<?php

namespace Northrook\Symfony\Core\Controller;

use Northrook\Symfony\Core\Autowire\CurrentRequest;
use Northrook\Symfony\Core\DependencyInjection\CoreController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;

final class PublicController extends CoreController
{
    public const STYLESHEETS          = [ 'dir.core.assets/styles' ];
    public const DYNAMIC_TEMPLATE_DIR = 'public';

    public function __construct(
        protected readonly CurrentRequest $request,
        // protected readonly DocumentService             $document,
        // protected readonly StylesheetGenerationService $stylesheet,
    ) {
        // if ( false === $this->request->type()-> ) {
        //     $this->stylesheet->includeStylesheets( $this::STYLESHEETS )->save( force : true );
        // }
        //
        // $this->document->stylesheet( 'dir.cache/styles/styles.css' );
        //
        // $this->document->script( 'dir.assets/scripts/core.js' );
        //
        // $this->document->body();
    }

    public function index(
        ?string      $route,
        Request      $request,
        RequestStack $requestStack,
    ) : Response {

        dd( $this );
        // print_r( Path::getParameter( `path.favicon` ) );

        return $this->response(
            template   : 'public.latte',
            parameters : [ 'route' => $route ],
        );
    }

}