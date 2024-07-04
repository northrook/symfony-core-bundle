<?php

/*-------------------------------------------------------------------/
   HTTP Error Handler

/-------------------------------------------------------------------*/

declare( strict_types = 1 );

namespace Northrook\Symfony\Core\EventListener;

use Northrook\Symfony\Core\Autowire\CurrentRequest;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

final readonly class HttpExceptionListener
{
    public function __construct(
        private CurrentRequest   $request,
        private ?LoggerInterface $logger,
    ) {}

    public function __invoke( ExceptionEvent $event ) : void {

        $exception = $event->getThrowable();

        if ( $exception instanceof HttpExceptionInterface ) {
            $this->HttpException( $event, $exception );
        }

    }

    public function HttpException(
        ExceptionEvent         $event,
        HttpExceptionInterface $exception,
    ) : void {

        dump( $event, $exception );

        $this->logger?->error( 'Manual error for 404' );
        $event->setResponse(
            new Response( 'Not found, sadly.' ),
        );

        return;


        // $this->document->stylesheet( 'dir.cache/styles/styles.css' );
        // $this->document->script(
        //     'dir.assets/scripts/core.js',
        //     'dir.assets/scripts/components.js',
        // );
        //
        // $template   = $exception->template ?? 'error.latte';
        // $parameters = array_merge(
        //     [
        //         'message' => $exception->getMessage(),
        //         'status'  => $exception->getStatusCode(),
        //         'content' => $exception->content ?? null,
        //     ], $exception->parameters ?? [],
        // );
        //
        // if ( $parameters[ 'content' ] instanceof Template ) {
        //     $template = $parameters[ 'content' ];
        //     unset( $parameters[ 'content' ] );
        //     $parameters[ 'content' ] = $template->addData( $parameters );
        // }
        //
        // $event->setResponse(
        //     $this->response(
        //         $template,
        //         $parameters,
        //         $exception->getStatusCode(),
        //     ),
        // );

    }
}