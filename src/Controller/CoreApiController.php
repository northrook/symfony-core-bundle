<?php

namespace Northrook\Symfony\Core\Controller;

use Northrook\Favicon\FaviconBundle;
use Northrook\Logger\Status\HTTP;
use Northrook\Symfony\Core\Services\PathfinderService;
use Psr\Log\LoggerInterface;
use SVG\SVG;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;


final class CoreApiController
{

    public function __construct(
        private readonly PathfinderService $pathfinder,
        private readonly ?LoggerInterface  $logger,
    ) {}

    public function favicon( string $action, FaviconBundle $generator ) : Response {

        $generator->load(
            SVG::fromString(
                '<svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M8.00012 0L14.9283 4V12L8.00012 16L1.07192 12V4L8.00012 0Z" fill="url(#paint0_linear_137_96)"/>
                        <path d="M13.1962 11V5L8.00001 8L8 14L13.1962 11Z" fill="url(#paint1_linear_137_96)"/>
                        <path opacity="0.75" d="M8.00001 11.0566L8.00002 8L10.6962 6.44338V3.5L5.50001 6.5L5.5 12.5L8.00001 11.0566Z" fill="url(#paint2_linear_137_96)"/>
                        <path opacity="0.5" d="M5.50001 9.55662L5.50002 6.5L8.19615 4.94338V2L3.00001 5L3 11L5.50001 9.55662Z" fill="url(#paint3_linear_137_96)"/>
                        <defs>
                        <linearGradient id="paint0_linear_137_96" x1="-1" y1="3" x2="19.5" y2="14.5" gradientUnits="userSpaceOnUse">
                        <stop stop-color="#004CFF"/>
                        <stop offset="1" stop-color="#4CFFFF"/>
                        </linearGradient>
                        <linearGradient id="paint1_linear_137_96" x1="8" y1="8" x2="16" y2="13" gradientUnits="userSpaceOnUse">
                        <stop stop-color="#4CFFFF"/>
                        <stop offset="1" stop-color="#4CFFFF" stop-opacity="0"/>
                        </linearGradient>
                        <linearGradient id="paint2_linear_137_96" x1="5.5" y1="6.5" x2="15" y2="12" gradientUnits="userSpaceOnUse">
                        <stop stop-color="#4CFFFF"/>
                        <stop offset="1" stop-color="#4CFFFF" stop-opacity="0"/>
                        </linearGradient>
                        <linearGradient id="paint3_linear_137_96" x1="3" y1="5" x2="15" y2="12" gradientUnits="userSpaceOnUse">
                        <stop stop-color="#4CFFFF"/>
                        <stop offset="1" stop-color="#4CFFFF" stop-opacity="0"/>
                        </linearGradient>
                        </defs>
                        </svg>
                        ',
            ),
        );

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