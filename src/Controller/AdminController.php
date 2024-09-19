<?php

namespace Northrook\Symfony\Core\Controller;

use Northrook\Symfony\Core\DependencyInjection\CoreController;
use Northrook\Symfony\Core\Http\DocumentResponse;
use Northrook\Symfony\Core\ResponseHandler\ResponsePayload;
use Northrook\Symfony\Core\Security\Authentication;
use Northrook\Symfony\Core\Service\CurrentRequest;
use Northrook\Symfony\Core\Service\StylesheetGenerator;
use Northrook\UI\Model\Menu;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Profiler\Profiler;


// use Northrook\Symfony\Core\Components\Menu\Menu;
// use Northrook\Symfony\Core\Components\Menu\Navigation;

final class AdminController extends CoreController
{

    public function __construct(
            protected readonly CurrentRequest $request,
            protected readonly Authentication $auth,
    )
    {
        // $this->document
        //         ->set(
        //                 'Admin',
        //                 'This is an example admin template.',
        //         )->body(
        //                 id               : 'admin',
        //                 style            : [ '--sidebar-width' => '160px' ],
        //                 sidebar_expanded : true,
        //         )->theme(
        //                 '#ff0000',
        //                 'light',
        //         )
        //         ->asset( Style::from( 'path.admin.stylesheet', 'core-styles' ) )
        //         ->asset( Script::from( 'dir.assets/scripts/*.js', 'core-scripts' ) )
        // ;

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

    /**
     * # EntryPoint
     *
     * This is where all `domain.tld/admin/~` requests will be routed to.
     *
     * - `IF` the incoming request is tagged as a `HX` request, return only the content.
     * - `IF` the incoming request is generic, return full {@see DocumentResponse}.
     *
     * ---
     *
     * The `content` for each request will originate from a method either within this class,
     * or from any class within the {@see \Northrook\Symfony\Core\Controller\Admin} namespace.
     *
     * ---
     *
     * @param ?string                                              $route
     * @param \Northrook\Symfony\Core\Service\StylesheetGenerator  $generator
     * @param \Symfony\Component\HttpKernel\Profiler\Profiler      $profiler
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function index(
            ?string             $route,
            StylesheetGenerator $generator,
            Profiler            $profiler,
    ) : Response
    {
        $profiler->disable();

        // $generator->admin->addSource( 'dir.assets/admin/styles' );
        //
        // if ( $generator->admin->save(
        //         force : true,
        // ) ) {
        //     Toast::info( 'Admin Stylesheet updated.' );
        // };

        return new Response( __METHOD__ );

        // $this->document->title( 'testme' )->description( 'we describe things' );

        // $this->document->robots( 'test', 'gooblebot')->robots( 'another', 'all');

        // dump( $this->document->getMetaTags() );
        // return $this->view( $route );

        // return $this->response(
        //         content    : $this->request->isHtmx ? $template : 'admin.latte',
        //         parameters : [
        //                              'template' => $template,
        //                              'route'    => $route,
        //                              // 'navigation' => $this->getNavigation(),
        //                      ],
        // );
    }

    public function dashboard() : ResponsePayload
    {
        return new ResponsePayload(
                'admin/dashboard.latte',
        );
    }

    private function sidebarMenu() : Menu
    {
        dump( "Called " . __METHOD__ );
        $sidebar = new Menu( 'sidebar', $this->request->routeRoot );

        $sidebar->items(
                Menu::link( title : 'Dashboard', href : '/dashboard', icon : 'ui:dashboard' )
                    ->submenu(
                            Menu::link( title : 'Content', href : '/content', icon : 'ui:layers' ),
                            Menu::link( title : 'Analytics', href : '/analytics', icon : 'ui:bar-chart' ),
                    )
                //  ->actions( Element ... $action ) or custom Action class
                ,
                Menu::link( title : 'Website', href : '/content', icon : 'ui:hex-bolt' )
                    ->submenu(
                            Menu::link( title : 'Pages', href : '/pages' ),
                            Menu::link( title : 'Products', href : '/products' ),
                            Menu::link( title : 'Services', href : '/services' ),
                            Menu::link( title : 'Articles', href : '/articles' ),
                            Menu::link( title : 'Taxonomies', href : '/taxonomies' ),
                    ),
                Menu::item( title : 'Settings', href : 'settings', icon : 'ui:settings' )
                    ->submenu(
                            Menu::link( title : 'General', href : '/settings' ),
                            Menu::link( title : 'Appearance', href : '/appearance' ),
                            Menu::link( title : 'Accounts', href : '/accounts' ),
                            Menu::link( title : 'Performance', href : '/performance' ),
                    ),
                Menu::link(
                        title      : 'User',
                        href       : './admin/user/profile/',
                        icon       : 'user',
                        attributes : [ 'class' => 'mt-auto' ],
                ),
        );

        return $sidebar;
    }
}