<?php

namespace Northrook\Symfony\Core\Controller;

use Northrook\Symfony\Core\Components\Menu\Menu;
use Northrook\Symfony\Core\Components\Menu\Navigation;
use Northrook\Symfony\Core\DependencyInjection\CoreDependencies;
use Northrook\Symfony\Core\DependencyInjection\Trait\CorePropertiesPromoter;
use Northrook\Symfony\Core\DependencyInjection\Trait\LatteRenderer;
use Northrook\Symfony\Core\DependencyInjection\Trait\NotificationServices;
use Northrook\Symfony\Core\DependencyInjection\Trait\ResponseMethods;
use Northrook\Symfony\Core\DependencyInjection\Trait\SecurityServices;
use Northrook\Symfony\Core\Services\MailerService;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authorization\Voter\AuthenticatedVoter;

final class AdminController
{
    use ResponseMethods, LatteRenderer, NotificationServices, SecurityServices, CorePropertiesPromoter;

    public const STYLESHEETS          = [ 'dir.core.assets/styles' ];
    public const DYNAMIC_TEMPLATE_DIR = 'admin';

    public function __construct(
        protected readonly CoreDependencies $get,
    ) {
        $this->denyAccessUnlessGranted( AuthenticatedVoter::IS_AUTHENTICATED_FULLY );

        if ( false === $this->request->is( 'hypermedia' ) ) {
            $this->stylesheet->includeStylesheets( $this::STYLESHEETS )->save( force : true );
        }

        $this->document->stylesheet( 'dir.cache/styles/styles.css' );

        $this->document->script( 'dir.assets/scripts/core.js' )
                       ->script( 'dir.assets/scripts/components.js' )
                       ->script( 'dir.assets/scripts/navigation.js' )
                       ->script( 'dir.assets/scripts/interactions.js' )
                       ->script( 'dir.assets/scripts/admin.js' );


        $this->document->body(
            class            : 'core-admin',
            style            : [ '--sidebar-width' => '120px' ],
            sidebar_expanded : true,
        );

    }

    private function getNavigation() : Navigation {

        $navigation = new Navigation( 'admin', $this->request->route );

        $navigation->add(
            [
                'dashboard' => Menu::item( 'Dashboard', 'layout-panel-top:lucide', 'dashboard' )->add(
                    [
                        'content'   => Menu::item( 'Content', 'app-window:lucide', 'content' ),
                        'analytics' => Menu::item( 'Analytics', 'bar-chart-2:lucide', 'analytics' ),
                    ],
                ),
                'website'   => Menu::item( 'Website', 'app-window:lucide', 'website' )->add(
                    [
                        Menu::item( 'Pages', 'panel-top:lucide', 'pages' ),
                        Menu::item( 'Articles', 'newspaper:lucide', 'posts' ),
                        Menu::item( 'Taxonomies', 'tags:lucide', 'taxonomies' ),
                        Menu::item( 'Users', 'user:lucide', 'users' ),
                        Menu::item( 'Users', 'user:lucide', 'users' ),
                        Menu::item( 'Users', 'user:lucide', 'users' ),
                        Menu::item( 'Users', 'user:lucide', 'users' ),
                    ],
                ),
                'settings'  => Menu::item( 'Settings', 'settings:lucide', )->add(
                    [
                        Menu::item( 'Appearance', 'pencil-ruler:lucide', 'appearance' ),
                        Menu::item( 'Accounts', 'users:lucide', 'accounts' ),
                        Menu::item( 'Cache', 'server:lucide', 'accounts' ),
                    ],
                ),
            ],
        )->add(
            Menu::item( 'Ad-hoc' ),
        );

        return $navigation;
    }

//     protected function view( string $route, array $parameters = [] ) : Response {
//
//         $template = $this->dynamicTemplatePath();
//         if ( $this->request->headers( 'hx-request' ) ) {
//             // dd( $this->request);
//             $content = $this->render( $template, $parameters );
//
//             $head = <<<HEAD
// <head core="merge">
//     <title>Bananas</title>
//     <meta name="description" content="This describes $route">
//     <link id="demo-stylesheet" remove>
//     <link rel="stylesheet" href="/css/site1.css">
//     <script src="/js/script1.js"></script>
//     <script src="/js/script2.js"></script>
// </head>
// HEAD;
//
//             return new Response(
//                 $head . $content,
//             );
//         }
//
//         // dump( $template );
//         return $this->response(
//             'admin.latte',
//             [
//                 'template'   => $template,
//                 'navigation' => $this->getNavigation(),
//             ],
//         );
//
//     }

    public function index(
        ?string       $route,
        MailerService $mailer,
        // ?Profiler $profiler
    ) : Response {
        // $this->document->title( 'testme' )->description( 'we describe things' );

        // $this->document->robots( 'test', 'gooblebot')->robots( 'another', 'all');

        // dump( $this->document->getMetaTags() );
        // return $this->view( $route );

        $template = $this->dynamicTemplatePath();

        // $this->onLatteRender();

        // dump( Settings::public() );
        // dd( $this->request->headerBag( has : 'hx-request'), $template );

        // dd( $this->latte );
        return $this->response(
            template   : $this->request->is( 'hypermedia' ) ? $template : 'admin.latte',
            parameters : [
                             'template'   => $template,
                             'route'      => $route,
                             'navigation' => $this->getNavigation(),
                         ],
        );
    }

    public function dashboard() : Response {
        return $this->response(
            'admin/dashboard.latte',
        );
    }

    public function api( string $action ) : Response {

        return new JsonResponse(
            [ 'action' => $action, ],
        );
    }

    public function search( string $action ) : Response {

        return new JsonResponse(
            [ 'action' => $action, ],
        );
    }

}