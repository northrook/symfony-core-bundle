<?php

namespace Northrook\Symfony\Core\Controller;

use Northrook\Logger\Log;
use Northrook\Symfony\Core\DependencyInjection\CoreController;
use Northrook\Symfony\Core\Security\Authentication;
use Northrook\Symfony\Core\Service\CurrentRequest;
use Northrook\Symfony\Service\DocumentService;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use const Cache\EPHEMERAL;

class EventController extends CoreController
{

    public function __construct(
            protected readonly CurrentRequest  $request,
            protected readonly DocumentService $document,
            protected readonly Authentication  $auth,
    ) {}

    public function __invoke( ExceptionEvent $event ) : void
    {
        $exception = $event->getThrowable();

        match ( true ) {
            $exception instanceof HttpExceptionInterface => $this->HttpException( $event, $exception ),
            default                                      => $this->handleException( $event, $exception ),
        };
    }

    public function handleException( ExceptionEvent $event, \Throwable $exception ) : void
    {
        dump( $exception, $event );
        $event->setResponse(
                new Response(
                        'Exception occurd. Soft response.',
                        500,
                ),
        );
    }

    public function HttpException(
            ExceptionEvent         $event,
            HttpExceptionInterface $exception,
    ) : void
    {
        dump( $event, $exception );

        Log::error( 'Manual error for 404' );

        $this->document
                ->set(
                        'Welcome!',
                )->body(
                        id : 'public',
                )->asset(
                                      [
                                              'path.admin.stylesheet',
                                              'dir.assets/scripts/*.js',
                                      ],
                        persistence : EPHEMERAL,
                );

        $event->setResponse(
                new Response( 'Not found, sadly.' ),
        );

        return;
    }
}