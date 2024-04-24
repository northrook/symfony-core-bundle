<?php

namespace Northrook\Symfony\Core\Controller;

use Northrook\Symfony\Core\Components\Menu\Menu;
use Northrook\Symfony\Core\Components\Menu\Navigation;
use Northrook\Symfony\Core\File;
use Northrook\Symfony\Core\Services\CurrentRequestService;
use Northrook\Symfony\Core\Services\MailerService;
use Northrook\Symfony\Core\Services\PathfinderService;
use Northrook\Symfony\Core\Services\SecurityService;
use Northrook\Symfony\Core\Services\StylesheetGenerationService;
use Northrook\Symfony\Latte\Core;
use Northrook\Symfony\Latte\Parameters;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authorization\Voter\AuthenticatedVoter;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Stopwatch\Stopwatch;

final readonly class AdminController
{
    use CoreControllerTrait;

    public function __construct(
        protected RouterInterface           $router,
        protected HttpKernelInterface       $httpKernel,
        protected ?SerializerInterface      $serializer,
        protected SecurityService           $security,
        protected CurrentRequestService     $request,
        private PathfinderService           $pathfinder,
        private ParameterBagInterface       $parameters,
        private StylesheetGenerationService $stylesheet,
        protected Core\Environment          $latte,
        protected Parameters\Document       $document,
        protected ?LoggerInterface          $logger,
        private ?Stopwatch                  $stopwatch,
    ) {

        $navigation = new Navigation(
            $this->route(),
        );

        $navigation->add(
            [
                'dashboard' => Menu::item( 'Dashboard', 'info', 'dashboard' )->add(
                    [
                        'content'   => Menu::item( 'Content', 'info', 'content' ),
                        'analytics' => Menu::item( 'Analytics', 'info', 'analytics' ),
                    ],
                ),
                'website'   => Menu::item( 'Website', 'app-window', 'website' )->add(
                    [
                        Menu::item( 'Pages', 'panel-top', 'pages' ),
                        Menu::item( 'Articles', 'newspaper', 'posts' ),
                        Menu::item( 'Taxonomies', 'tags', 'taxonomies' ),
                        Menu::item( 'Users', 'user-2', 'users' ),
                    ],
                ),
                'settings'  => Menu::item( 'Settings', 'settings', )->add(
                    [
                        Menu::item( 'Appearance', 'pencil-ruler', 'appearance' ),
                        Menu::item( 'Accounts', 'users', 'accounts' ),
                    ],
                ),
            ],
        )->add(
            Menu::item( 'Ad-hoc' ),
        );

        // dd($navigation);

        $this->properties = [
            'navigation' => $navigation,
        ];


        $this->stylesheet->includeStylesheets(
            [
                'dir.core.assets/styles',
            ],
        );
        $path  = File::path( 'dir.cache/styles/styles.css' );
        $saved = $this->stylesheet->save( $path, true );

        $this->security->denyAccessUnlessGranted( AuthenticatedVoter::IS_AUTHENTICATED_FULLY );
        $this->document->addStylesheet( 'dir.cache/styles/styles.css' );
        $this->document->addScript(
            'dir.assets/scripts/core.js',
            'dir.assets/scripts/components.js',
            'dir.assets/scripts/interactions.js',
            'dir.assets/scripts/admin.js',

        );

        $this->document->body->style->add( [ '--sidebar-width' => '150px' ] );
        $this->document->body->set( 'sidebar-expanded', true );

    }

    public function index(
        ?string       $route,
        MailerService $mailer,
        // ?Profiler $profiler
    ) : Response {


        // $profiler?->disable();

        return $this->response(
            template   : 'admin.latte',
            parameters : [
                             'route' => $route,
                         ],
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