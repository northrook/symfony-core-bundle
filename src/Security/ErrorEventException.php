<?php

namespace Northrook\Symfony\Core\Security;

use Northrook\Elements\Render\Template;
use Northrook\Symfony\Core\EventListener\ExceptionListener;
use RuntimeException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

/**
 * Trigger a HTTP Error.
 *
 * The Exception is intercepted by {@see ExceptionListener}, which may render an error page.
 *
 *
 */
class ErrorEventException extends RuntimeException implements HttpExceptionInterface
{
    private int   $statusCode;
    private array $headers;

    public readonly ?string $content;
    public readonly string  $template;
    public readonly array   $parameters;

    public function __construct(
        ?string                  $message = null,
        int                      $status = Response::HTTP_NOT_FOUND,
        string | Template | null $content = null,
        string                   $template = null,
        array                    $parameters = [],
        array                    $headers = [],
        int                      $code = 0,
        ?\Throwable              $previous = null,
    ) {
        $this->statusCode = $status;
        $this->headers    = $headers;

        $this->content    = $content ?? $message;
        $this->template   = $template ?? 'error.latte';
        $this->parameters = array_merge(
            [
                'message' => $message,
                'content' => $content,
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