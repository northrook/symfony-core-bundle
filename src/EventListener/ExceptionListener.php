<?php

namespace Northrook\Symfony\Core\EventListener;

use Northrook\Symfony\Core\Security\ErrorEventException;
use Northrook\Symfony\Core\Services\CurrentRequestService;
use Northrook\Symfony\Core\Services\SecurityService;
use Northrook\Symfony\Core\Services\SettingsManagementService;
use Northrook\Symfony\Latte\Core\Environment;
use Northrook\Symfony\Latte\Parameters\Document;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

class ExceptionListener
{
    public function __construct(
        public readonly SecurityService           $security,
        public readonly CurrentRequestService     $request,
        public readonly SettingsManagementService $settings,
        public readonly Environment               $latte,
        public readonly Document                  $document,
        public readonly ?LoggerInterface          $logger,
    ) {}

    public function __invoke( ExceptionEvent $event ) : void {


        if ( $event->getThrowable() instanceof ErrorEventException ) {
            dd( 'CoreErrorEvent', $event->getThrowable() );
        }

        if ( $event->getThrowable() instanceof HttpExceptionInterface ) {
            dd( 'httpException', $event->getThrowable() );
        }


        // You get the exception object from the received event
        $exception = $event->getThrowable();
        $message   = sprintf(
            'My Error says: %s with code: %s',
            $exception->getMessage(),
            $exception->getCode(),
        );

        // Customize your response object to display the exception details
        $response = new Response();
        $response->setContent( $message );

        // HttpExceptionInterface is a special type of exception that
        // holds status code and header details
        if ( $exception instanceof HttpExceptionInterface ) {
            $response->setStatusCode( $exception->getStatusCode() );
            $response->headers->replace( $exception->getHeaders() );
        }
        else {
            $response->setStatusCode( Response::HTTP_INTERNAL_SERVER_ERROR );
        }

        // sends the modified response object to the event
        $event->setResponse( $response );
    }
}