<?php

namespace Northrook\Symfony\Core\Controller;


use Northrook\Favicon\FaviconBundle;
use Northrook\Symfony\Core\DependencyInjection\CoreController;
use Northrook\Symfony\Core\Facade\Logger;
use Northrook\Symfony\Core\Facade\Pathfinder;
use Northrook\Symfony\Core\Services\CurrentRequestService;
use Northrook\Symfony\Core\Services\PathfinderService;
use Northrook\Symfony\Core\Services\StylesheetGenerationService;
use Northrook\Type\Path;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;


final class ApiController extends CoreController
{

    public function __construct(
        protected readonly CurrentRequestService $request,
    ) {}

    public function stylesheet(
        string                      $bundle,
        StylesheetGenerationService $generator,
        PathfinderService           $pathfinder,
    ) : Response {

        $generator->includeStylesheets(
            [ 'dir.core.assets/styles', ],
        );

        $path = new Path( $pathfinder->get( 'dir.cache/styles/styles.css' ) );

        if ( !$path->exists ) {

            $this->addFlash(
                'error',
                'No stylesheet generated',
                'The save path is not valid. See the logs for more information.',
            );

            return $this->response( status : Response::HTTP_NO_CONTENT );
        }

        $saved = $generator->save( $path, true );

        return $this->response( status : Response::HTTP_ACCEPTED );
    }

    public function favicon( string $action, FaviconBundle $generator, PathfinderService $pathfinder ) : Response {

        $generator->load( Pathfinder::getParameter( 'path.favicon' ) );
        $generator->manifest->title = 'Symfony Playground';

        if ( 'generate' === $action ) {
            $generator->save( $pathfinder->get( 'dir.public' ) );
            $data = $generator->notices();
            Logger::info( 'Favicon generated', [ 'data' => $data ] );
            return new JsonResponse( $data, Response::HTTP_CREATED );
        }

        if ( 'purge' === $action ) {
            $data = $generator->purge( $pathfinder->get( 'dir.public' ) );
            Logger::info( 'Favicon purged', [ 'data' => $data ] );
            return new JsonResponse( $data, Response::HTTP_OK );
        }

        // TODO: expand with more info from Support::UserAgent
        Logger::error(
            'Unexpected action {action} for {route}.', [
            'route'  => __METHOD__,
            'action' => $action,
            'ip'     => $_SERVER[ 'REMOTE_ADDR' ],
        ],
        );
        return $this->response( status : Response::HTTP_ACCEPTED );
    }
}