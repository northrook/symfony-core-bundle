<?php

namespace Northrook\Symfony\Core\Controller;

use Northrook\Assets\Script;
use Northrook\Assets\Style;
use Northrook\Symfony\Core\Admin\SidebarMenu;
use Northrook\Symfony\Core\Controller\Trait\ResponseMethods;
use Northrook\Symfony\Core\DependencyInjection\CoreController;
use Northrook\Symfony\Core\Facade\Toast;
use Northrook\Symfony\Core\Security\Authentication;
use Northrook\Symfony\Core\Service\CurrentRequest;
use Northrook\Symfony\Core\Service\StylesheetGenerator;
use Northrook\Symfony\Service\Document\DocumentService;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Profiler\Profiler;


// use Northrook\Symfony\Core\Components\Menu\Menu;
// use Northrook\Symfony\Core\Components\Menu\Navigation;

final class AdminController extends CoreController
{
    use ResponseMethods;


    public function __construct(
            protected readonly CurrentRequest  $request,
            protected readonly DocumentService $document,
            protected readonly SidebarMenu     $sidebar,
            protected readonly Authentication  $auth,
    )
    {
        $this->document
                ->set(
                        'Admin',
                        'This is an example admin template.',
                )->body(
                        id               : 'admin',
                        style            : [ '--sidebar-width' => '160px' ],
                        sidebar_expanded : true,
                )->theme(
                        '#ff0000',
                        'light',
                )
                ->asset( Style::from( 'path.admin.stylesheet', 'core-styles' ) )
                ->asset( Script::from( 'dir.assets/scripts/*.js', 'core-scripts' ) )
        ;

        // Auth::denyAccessUnlessGranted( AuthenticatedVoter::IS_AUTHENTICATED_FULLY );
        //
        // if ( false === $this->request->is( 'hypermedia' ) ) {
        //     $this->stylesheet->includeStylesheets( $this::STYLESHEETS )->save( force : true );
        // }
        //
        // $this->document->stylesheet( 'dir.cache/styles/styles.css' );
        //
        // $this->document->script( 'dir.assets/scripts/core.js' )
        //                ->script( 'dir.assets/scripts/components.js' )
        //                ->script( 'dir.assets/scripts/navigation.js' )
        //                ->script( 'dir.assets/scripts/interactions.js' )
        //                ->script( 'dir.assets/scripts/admin.js' );
        //
        //

    }

    public function index(
            ?string             $route,
            StylesheetGenerator $generator,
            Profiler            $profiler,
    ) : Response
    {
        $profiler->disable();

        $generator->admin->addSource( 'dir.assets/admin/styles' );

        if ( $generator->admin->save(
                force : true,
        ) ) {
            Toast::info( 'Admin Stylesheet updated.' );
        };

        // $this->document->title( 'testme' )->description( 'we describe things' );

        // $this->document->robots( 'test', 'gooblebot')->robots( 'another', 'all');

        // dump( $this->document->getMetaTags() );
        // return $this->view( $route );

        $template = $this->dynamicTemplatePath( 'admin' );

        return $this->response(
                content    : $this->request->isHtmx ? $template : 'admin.latte',
                parameters : [
                                     'template' => $template,
                                     'route'    => $route,
                                     // 'navigation' => $this->getNavigation(),
                             ],
        );
    }

    public function dashboard() : Response
    {
        return $this->response(
                'admin/dashboard.latte',
        );
    }

    public function api( string $action ) : Response
    {
        return new JsonResponse(
                [ 'action' => $action, ],
        );
    }

    public function search( string $action ) : Response
    {
        return new JsonResponse(
                [ 'action' => $action, ],
        );
    }

}