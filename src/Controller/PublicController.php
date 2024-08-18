<?php


namespace Northrook\Symfony\Core\Controller;

use Northrook\Symfony\Core\Autowire\Authentication;
use Northrook\Symfony\Core\Autowire\CurrentRequest;
use Northrook\Symfony\Core\DependencyInjection\CoreController;
use Northrook\Symfony\Core\Facade\Toast;
use Northrook\Symfony\Core\Service\StylesheetGenerator;
use Northrook\Symfony\Service\Document\DocumentService;
use Symfony\Component\HttpFoundation\Response;

final class PublicController extends CoreController
{
    public const STYLESHEETS          = [ 'dir.core.assets/styles' ];
    public const DYNAMIC_TEMPLATE_DIR = 'public';

    public function __construct(
        protected readonly CurrentRequest  $request,
        protected readonly DocumentService $document,
        protected readonly Authentication  $auth,
    ) {
        $this->document
            ->set(
                'Welcome!',
            )->body(
                id : 'public',
            )->asset(
                'path.public.stylesheet',
            );

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
        ?string             $route,
        StylesheetGenerator $generator,
    ) : Response {

        $generator->public->addSource( 'dir.assets/public/styles' );

        if ( $generator->public->save() ) {
            Toast::info( 'Public Stylesheet updated.' );
        };

        return $this->response(
            content    : 'welcome.latte',
            parameters : [ 'route' => $route ],
        );
    }

}