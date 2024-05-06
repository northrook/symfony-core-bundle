<?php

namespace Northrook\Symfony\Core\Controller;

use Northrook\Favicon\FaviconBundle;
use Northrook\Symfony\Core\DependencyInjection\CoreDependencies;
use Northrook\Symfony\Core\DependencyInjection\Trait\CorePropertiesPromoter;
use Northrook\Symfony\Core\DependencyInjection\Trait\LatteRenderer;
use Northrook\Symfony\Core\DependencyInjection\Trait\NotificationServices;
use Northrook\Symfony\Core\DependencyInjection\Trait\ResponseMethods;
use Northrook\Symfony\Core\DependencyInjection\Trait\SecurityServices;
use Northrook\Symfony\Core\File;
use Northrook\Symfony\Core\Services\StylesheetGenerationService;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;


final readonly class ApiController
{
    use ResponseMethods, LatteRenderer, NotificationServices, SecurityServices, CorePropertiesPromoter;

    public function __construct(
        protected readonly CoreDependencies $get,
    ) {}

    // TODO: Public Stylesheets only. Admin and Component styles are precompiled.
    // public function stylesheet( string $action, StylesheetGenerationService $generator ) : Response {
    //
    // }

    public function stylesheet( string $bundle, StylesheetGenerationService $generator ) : Response {

        $generator->includeStylesheets(
            [
                'dir.core.assets/styles',
            ],
        );
        $path  = File::path( 'dir.cache/styles/styles.css' );
        $saved = $generator->save( $path, true );

        return new Response(
            $this->injectFlashBagNotifications(),
        );
    }

    public function favicon( string $action, FaviconBundle $generator ) : Response {

        $favicon = $this->parameterBag->get( 'path.favicon' );

        $generator->load( $favicon );
        $generator->manifest->title = 'Symfony Playground';

        if ( 'generate' === $action ) {
            $generator->save( File::path( 'dir.public' ) );
            $data = $generator->notices();
            $this->logger->info( 'Favicon generated', [ 'data' => $data ] );
            return new JsonResponse( $data, Response::HTTP_CREATED );
        }

        if ( 'purge' === $action ) {
            $data = $generator->purge( File::path( 'dir.public' ) );
            $this->logger->info( 'Favicon purged', [ 'data' => $data ] );
            return new JsonResponse( $data, Response::HTTP_OK );
        }

        // TODO: expand with more info from Support::UserAgent
        $this->logger->error(
            'Unexpected action {action} for {route}.', [
            'route'  => __METHOD__,
            'action' => $action,
            'ip'     => $_SERVER[ 'REMOTE_ADDR' ],
        ],
        );
        return new Response( status : Response::HTTP_ACCEPTED );
    }
}