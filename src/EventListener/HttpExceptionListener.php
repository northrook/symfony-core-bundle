<?php

namespace Northrook\Symfony\Core\EventListener;

use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

final readonly class HttpExceptionListener
{
    private \Throwable $exception;

    public function __construct(
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

        dump( $this->exception );

        $this->logger?->error( 'Manual error for 404' );
        $event->setResponse(
            new Response( 'Not found, sadly.' ),
        );

    }
}