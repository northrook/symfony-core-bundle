<?php

namespace Northrook\Symfony\Core\Controller;

use Northrook\Core\Type\PathType;
use Northrook\Favicon\FaviconBundle;
use Northrook\Symfony\Core\DependencyInjection\CoreDependencies;
use Northrook\Symfony\Core\DependencyInjection\Trait\CorePropertiesPromoter;
use Northrook\Symfony\Core\DependencyInjection\Trait\LatteRenderer;
use Northrook\Symfony\Core\DependencyInjection\Trait\NotificationServices;
use Northrook\Symfony\Core\DependencyInjection\Trait\ResponseMethods;
use Northrook\Symfony\Core\DependencyInjection\Trait\SecurityServices;
use Northrook\Symfony\Core\Services\PathfinderService;
use Northrook\Symfony\Core\Services\StylesheetGenerationService;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;


final class ApiController
{
    use ResponseMethods, LatteRenderer, NotificationServices, SecurityServices, CorePropertiesPromoter;

    public function __construct(
        protected readonly CoreDependencies $get,
        private readonly PathfinderService $pathfinder,
    ) {}

    public function stylesheet( string $bundle, StylesheetGenerationService $generator ) : Response {

        $generator->includeStylesheets(
            [ 'dir.core.assets/styles', ],
        );

        $path  = new PathType($this->pathfinder->get( 'dir.cache/styles/styles.css' ));

        if ( ! $path->exists) {

            $this->addFlash(
                'error',
                 'No stylesheet generated',
                  'The save path is not valid. See the logs for more information.',
                   );

            return new Response(
                $this->injectFlashBagNotifications(),
             Response::HTTP_NO_CONTENT,
             );
        }

        $saved = $generator->save( $path, true );

        return new Response(
            $this->injectFlashBagNotifications(),
            Response::HTTP_NO_CONTENT,
        );
    }

    public function favicon( string $action, FaviconBundle $generator ) : Response {

        $favicon = $this->parameterBag->get( 'path.favicon' );

        $generator->load( $favicon );
        $generator->manifest->title = 'Symfony Playground';

        if ( 'generate' === $action ) {
            $generator->save( $this->pathfinder->get( 'dir.public' ) );
            $data = $generator->notices();
            $this->logger->info( 'Favicon generated', [ 'data' => $data ] );
            return new JsonResponse( $data, Response::HTTP_CREATED );
        }

        if ( 'purge' === $action ) {
            $data = $generator->purge( $this->pathfinder->get( 'dir.public' ) );
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