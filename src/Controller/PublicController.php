<?php

namespace Northrook\Symfony\Core\Controller;

use Northrook\Symfony\Core\DependencyInjection\CoreController;
use Northrook\Symfony\Core\Security\Authentication;
use Northrook\Symfony\Core\Service\CurrentRequest;
use Northrook\Symfony\Core\Service\StylesheetGenerator;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Profiler\Profiler;


final class PublicController extends CoreController
{
    public const string DYNAMIC_TEMPLATE_DIR = 'public';

    public function __construct(
            protected readonly CurrentRequest $request,
            protected readonly Authentication $auth,
    )
    {
        // $this->document
        //         ->set(
        //                 'Welcome!',
        //         )->body(
        //                 id : 'public',
        //         )
        //         ->asset( Style::from( 'path.public.stylesheet', 'core-styles' ) )
        //         ->asset( Script::from( 'dir.assets/scripts/*.js', 'core-scripts' ) )
        // ;
    }

    public function index(
            ?string             $route,
            StylesheetGenerator $generator,
            Profiler            $profiler,
    ) : Response
    {
        // $profiler->disable();
        return new Response( __METHOD__ );

        // $generator->public->addSource( 'dir . assets /public/styles' );
        //
        // if ( $generator->public->save(
        //         force : true,
        // ) ) {
        //     Toast::info( 'public Stylesheet updated . ' );
        // };
        //
        // $this->document->set(
        //         title : \ucfirst( $route ),
        // );
        //
        // return $this->response(
        //         content    : $this->template( $route ),
        //         parameters : [ 'route' => $route ],
        // );
    }

    private function template( ?string $route ) : string
    {
        return match ( $route ) {
                   'demo'  => 'demo',
                   default => 'welcome',
               } . '.latte';
    }

}