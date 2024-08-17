<?php

declare( strict_types = 1 );

namespace Northrook\Symfony\Core\ErrorHandler;

use RuntimeException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Throwable;

/**
 * Trigger an HTTP Error.
 *
 * The Exception is intercepted by {@see ExceptionListener}, which may render an error page.
 *
 *
 */
final class ErrorEventException extends RuntimeException implements HttpExceptionInterface
{
    private int   $statusCode;
    private array $headers;

    public readonly string $template;
    public readonly array  $parameters;

    // TODO : When the error.latte template is fleshed out, hint at default parameters

    public function __construct(
        ?string    $message = null,
        array      $parameters = [
            'blurb' => null, // Additional context
            'link'  => null, // Return link, etc
        ],
        string     $template = 'error.latte',
        int        $status = Response::HTTP_NOT_FOUND,
        array      $headers = [],
        int        $code = 0,
        ?Throwable $previous = null,
    ) {
        $this->statusCode = $status;
        $this->headers    = $headers;

        $this->template   = $template;
        $this->parameters = array_merge(
            [
                'message' => $message ?? $previous->getMessage() ?? 'Not found',
                'status'  => $status,
            ], $parameters,
        );

        parent::__construct( $message, $code, $previous );
    }

    public function getStatusCode() : int {
        return $this->statusCode;
    }

    public function getHeaders() : array {
        return $this->headers;
    }

    public function setHeaders( array $headers ) : self {
        $this->headers = $headers;

        return $this;
    }
}