<?php

namespace Northrook\Symfony\Core\Controller;

use Northrook\Get;
use Northrook\Symfony\Core\Autowire\Authentication;
use Northrook\Symfony\Core\Autowire\CurrentRequest;
use Northrook\Symfony\Core\DependencyInjection\CoreController;
use Northrook\Symfony\Core\Facade\Toast;
use Northrook\Symfony\Core\Service\StylesheetGenerator;
use Northrook\Symfony\Service\Document\DocumentService;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Profiler\Profiler;
use const Cache\EPHEMERAL;


final class PublicController extends CoreController
{

    public const STYLESHEETS          = [ 'dir.core.assets/styles' ];
    public const DYNAMIC_TEMPLATE_DIR = 'public';

    public function __construct(
        protected readonly CurrentRequest  $request,
        protected readonly DocumentService $document,
        protected readonly Authentication  $auth,
    )
    {
        $this->document
            ->set(
                'Welcome!',
            )->body(
                id : 'public',
            )->asset(
                              [
                                  'path.public.stylesheet',
                                  Get::path( 'dir.core.assets/scripts/debug.js' ),
                                  Get::path( 'dir.core.assets/scripts/core.js' ),
                                  Get::path( 'dir.core.assets/scripts/elements.js' ),
                                  Get::path( 'dir.core.assets/scripts/functions.js' ),
                                  // Get::path( 'dir.core.assets/scripts/notifications.js' ),
                              ],
                persistence : EPHEMERAL,
            )
        ;
    }

    public function index(
        ?string             $route,
        StylesheetGenerator $generator,
        Profiler            $profiler,
    ) : Response
    {
        $profiler->disable();

        $generator->public->addSource( 'dir.assets/public/styles' );

        if ( $generator->public->save(
            force : true,
        ) ) {
            Toast::info( 'Public Stylesheet updated.' );
        };

        $this->document->set(
            title : \ucfirst( $route ),
        );

        return $this->response(
            content    : $this->template( $route ),
            parameters : [ 'route' => $route ],
        );
    }

    private function template( ?string $route ) : string
    {
        return match ( $route ) {
                   'demo'  => 'demo',
                   default => 'welcome',
               } . '.latte';
    }

}