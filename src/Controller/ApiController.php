<?php

namespace Northrook\Symfony\Core\Controller;

use Northrook\Favicon\FaviconBundle;
use Northrook\Logger\Status\HTTP;
use Northrook\Symfony\Core\File;
use Northrook\Symfony\Core\Services\PathfinderService;
use Northrook\Symfony\Core\Services\StylesheetGenerationService;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;


final readonly class ApiController
{
    use CoreControllerTrait;

    public function __construct(
        private PathfinderService     $pathfinder,
        private ParameterBagInterface $parameters,
        private ?LoggerInterface      $logger,
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

        return new JsonResponse(
            [
                'bundle' => $bundle,
                'saved'  => $bundle,
                'path'   => $path,
            ],
        );
    }

    public function favicon( string $action, FaviconBundle $generator ) : Response {

        $favicon = $this->parameters->get( 'path.favicon' );

        $generator->load( $favicon );
        $generator->manifest->title = 'Symfony Playground';

        if ( 'generate' === $action ) {
            $generator->save( $this->pathfinder->get( 'dir.public' ) );
            $data = $generator->notices();
            $this->logger->info( 'Favicon generated', [ 'data' => $data ] );
            return new JsonResponse( $data, HTTP::CREATED );
        }

        if ( 'purge' === $action ) {
            $data = $generator->purge( $this->pathfinder->get( 'dir.public' ) );
            $this->logger->info( 'Favicon purged', [ 'data' => $data ] );
            return new JsonResponse( $data, HTTP::OK );
        }

        // TODO: expand with more info from Support::UserAgent
        $this->logger->error(
            'Unexpected action {action} for {route}.', [
            'route'  => __METHOD__,
            'action' => $action,
            'ip'     => $_SERVER[ 'REMOTE_ADDR' ],
        ],
        );
        return new Response( status : HTTP::ACCEPTED );
    }
}