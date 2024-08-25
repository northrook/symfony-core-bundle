<?php

namespace Northrook\Symfony\Core\Controller;

use Northrook\Get;
use Northrook\Logger\Log;
use Northrook\Symfony\Core\Autowire\Authentication;
use Northrook\Symfony\Core\Autowire\CurrentRequest;
use Northrook\Symfony\Core\DependencyInjection\CoreController;
use Northrook\Symfony\Service\Document\DocumentService;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use const Northrook\Cache\EPHEMERAL;

class EventController extends CoreController {

    public function __construct(
        protected readonly CurrentRequest $request,
        protected readonly DocumentService $document,
        protected readonly Authentication $auth,
    ) {
        $this->document
            ->set(
                'Welcome!',
            )->body(
                id : 'public',
            )->asset(
                [
                    'path.public.stylesheet',
                    Get::path( 'dir.core.assets/scripts/debug.js' ),
                    Get::path( 'dir.core.assets/scripts/core.js' ),
                    Get::path( 'dir.core.assets/scripts/elements.js' ),
                    Get::path( 'dir.core.assets/scripts/functions.js' ),
                    // Get::path( 'dir.core.assets/scripts/notifications.js' ),
                ],
                persistence : EPHEMERAL,
            );
    }

    public function __invoke( ExceptionEvent $event ) : void {

        $exception = $event->getThrowable();

        if ( $exception instanceof HttpExceptionInterface ) {
            $this->HttpException( $event, $exception );
        }

    }


    public function HttpException(
        ExceptionEvent $event,
        HttpExceptionInterface $exception,
    ) : void {

        dump( $event, $exception );

        Log::error( 'Manual error for 404' );

        $event->setResponse(
            new Response( 'Not found, sadly.' ),
        );

        return;
    }
}