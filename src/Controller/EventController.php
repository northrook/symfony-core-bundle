<?php

namespace Northrook\Symfony\Core\Controller;

use Northrook\Logger\Log;
use Northrook\Symfony\Core\Autowire\Authentication;
use Northrook\Symfony\Core\DependencyInjection\CoreController;
use Northrook\Symfony\Core\Service\CurrentRequest;
use Northrook\Symfony\Service\Document\DocumentService;
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

        if ( $exception instanceof HttpExceptionInterface ) {
            $this->HttpException( $event, $exception );
        }
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
                )
        ;

        $event->setResponse(
                new Response( 'Not found, sadly.' ),
        );

        return;
    }
}