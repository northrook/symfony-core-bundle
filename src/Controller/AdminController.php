<?php

namespace Northrook\Symfony\Core\Controller;

use Northrook\Symfony\Core\DependencyInjection\CoreController;
use Northrook\Symfony\Core\Facade\Toast;
use Northrook\Symfony\Core\Response\ResponseHandler;
use Northrook\Symfony\Core\Security\Authentication;
use Northrook\Symfony\Core\Service\{CurrentRequest, StylesheetGenerator};
use Northrook\Symfony\Service\Document;
use Northrook\UI\Model\Menu;
use Symfony\Component\HttpFoundation\{JsonResponse, Response};
use Symfony\Component\HttpKernel\Profiler\Profiler;
use function Northrook\Cache\memoize;
use const Cache\EPHEMERAL;
use const Time\HOUR;

// use Northrook\Symfony\Core\Components\Menu\Menu;
// use Northrook\Symfony\Core\Components\Menu\Navigation;

final class AdminController extends CoreController
{
    public function __construct(
        protected readonly CurrentRequest $request,
        protected readonly Authentication $auth,
    ) {}

    /**
     * @param null|string $route
     *
     * @return callable
     */
    protected function resolveRequestedRoute( ?string $route ) : callable
    {
        if ( ! $route ) {
            return [$this, 'index'];
        }

        $method = $route;
        if ( ! \property_exists( $this, $method ) ) {
            $this->throwNotFoundException();
        }

        return [$this, $method];
    }

    /**
     * # EntryPoint.
     *
     * This is where all `domain.tld/admin/~` requests will be routed to.
     *
     * - `IF` the incoming request is tagged as a `HX` request, return only the content.
     * - `IF` the incoming request is generic, return full {@see DocumentResponse}.
     *
     * ---
     *
     * The `content` for each request will originate from a method either within this class,
     * or from any class within the {@see Admin} namespace.
     *
     * ---
     *
     * @param ?string             $route
     * @param Document            $document
     * @param ResponseHandler     $response
     * @param Profiler            $profiler
     * @param StylesheetGenerator $generator
     *
     * @return Response
     */
    public function index(
        ?string             $route,
        Document            $document,
        ResponseHandler     $response,
        Profiler            $profiler,
        StylesheetGenerator $generator,
    ) : Response {
        $response->template( 'admin/dashboard.latte' );

        if ( $this->request->isHtmx ) {
            return $response();
        }

        if ( $generator->admin->save(
            force : true,
        ) ) {
            Toast::info( 'Admin Stylesheet updated.' );
            Toast::warning( 'Admin Stylesheet updated?!' );
            Toast::error( 'Admin Stylesheet updated!!' );
            Toast::notice( 'Admin Stylesheet updated. 😏' );
        }

        $document(
            'Admin',
            'This is an example admin template.',
        )->body(
            id               : 'admin',
            style            : ['--sidebar-width' => '160px'],
            sidebar_expanded : true,
        )->theme(
            '#ff0000',
            'light',
        )->asset( 'core', 'admin' );

        // $response->document()
        //     ->asset( Style::from( 'path.admin.stylesheet', 'core-styles' ), minify : true )
        //     ->asset( Script::from( 'dir.assets/scripts/*.js', 'core-scripts' ), minify : true );

        $response->addParameter(
            'navigation',
            $this->sidebarMenu( HOUR ),
        );
        return $response();
    }

    public function api( string $action ) : Response
    {
        return new JsonResponse(
            ['action' => $action],
        );
    }

    public function search( string $action ) : Response
    {
        return new JsonResponse(
            ['action' => $action],
        );
    }

    private function sidebarMenu( ?int $cache = EPHEMERAL ) : Menu
    {
        // dump( "Called " . __METHOD__ );
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
                attributes : ['class' => 'mt-auto'],
            ),
        );
        return memoize( fn() => $sidebar, 'admin-sidebar-menu', $cache );
    }
}
