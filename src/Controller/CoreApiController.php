<?php

namespace Northrook\Symfony\Core\Controller;

use Northrook\Favicon\FaviconBundle;
use Northrook\Logger\Status\HTTP;
use Northrook\Symfony\Core\Services\PathfinderService;
use Psr\Log\LoggerInterface;
use SVG\SVG;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;


final class CoreApiController
{

    public function __construct(
        private readonly PathfinderService     $pathfinder,
        private readonly ParameterBagInterface $parameters,
        private readonly ?LoggerInterface      $logger,
    ) {}

    public function favicon( string $action, FaviconBundle $generator ) : Response {

        $generator->load(
            SVG::fromString(
                '<svg width="17" height="16" viewBox="0 0 17 16" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M15.5705 2.47228C15.023 2.49144 14.6454 2.16492 14.6275 1.66729C14.6221 1.48466 14.6689 1.32547 14.794 1.13943C14.9162 0.900616 14.942 0.873383 14.9387 0.769078C14.928 0.456199 14.4551 0.443866 14.3257 0.450235C12.5506 0.508571 12.0827 2.90477 11.7035 4.85273L11.5174 5.87993C12.5395 6.02977 13.2653 5.84503 13.6702 5.58322C14.2401 5.21373 13.5109 4.83358 13.6025 4.41257C13.6961 3.98348 14.0861 3.77702 14.3963 3.76808C14.8302 3.75786 15.14 4.2074 15.1302 4.66416C15.1145 5.41891 14.112 6.45717 12.1083 6.41502C11.864 6.40906 11.6392 6.39205 11.4297 6.36692L11.0517 8.45408L11.041 8.50431C10.7032 10.0802 10.2459 12.2141 8.65423 14.0784C7.27159 15.7215 5.86936 15.9769 5.24061 15.9982C4.06571 16.0378 3.28626 15.4115 3.25817 14.5751C3.23049 13.7655 3.94694 13.3227 4.41732 13.307C5.04479 13.2857 5.47858 13.7403 5.49517 14.2644C5.51094 14.7075 5.27977 14.8459 5.12695 14.9297C5.02437 15.0114 4.87026 15.0957 4.87622 15.2787C4.88006 15.3566 4.96476 15.5358 5.22487 15.5274C5.72208 15.5108 6.05155 15.2652 6.28227 15.1008C7.42697 14.1468 7.86797 12.4841 8.44479 9.45744L8.56569 8.72354L8.5698 8.70292C8.7654 7.72363 8.98306 6.63386 9.31363 5.54832C8.50608 4.94085 8.0225 4.18739 6.93743 3.89323C6.19331 3.69102 5.73953 3.86258 5.42069 4.26571C5.0431 4.74334 5.16868 5.36485 5.53391 5.72966L6.13627 6.39544C6.87527 7.25023 7.27967 7.91431 7.12771 8.80868C6.88718 10.236 5.1857 11.3305 3.17601 10.7128C1.46005 10.1841 1.13908 8.96874 1.34512 8.29956C1.52689 7.7104 1.99472 7.5993 2.45277 7.7402C2.94274 7.89089 3.13472 8.48643 2.99425 8.9449C2.97848 8.99302 2.95338 9.07517 2.90187 9.18456C2.84526 9.311 2.74011 9.42125 2.69414 9.56897C2.58387 9.92782 3.0764 10.1828 3.41993 10.2884C4.18702 10.5242 4.93582 10.1228 5.1261 9.50172C5.30276 8.93044 4.94135 8.53241 4.79151 8.37917L4.06528 7.60101C3.73239 7.23023 3.00063 6.19793 3.35777 5.03791C3.49485 4.59137 3.78432 4.1163 4.20534 3.80298C5.09291 3.14146 6.05793 3.03249 6.97701 3.29641C8.16639 3.63951 8.73809 4.42577 9.47922 5.03323C9.89342 3.81704 10.4681 2.62595 11.3327 1.62174C12.113 0.705658 13.1619 0.04329 14.3636 0.00198377C15.5641 -0.0371769 16.4711 0.506442 16.5014 1.3676C16.5134 1.73541 16.3022 2.44844 15.5705 2.47228Z" fill="#004CFF"/></svg>',
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