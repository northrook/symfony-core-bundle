<?php

declare(strict_types=1);

namespace Northrook\Symfony\Core\DependencyInjection;

use Exception;
use Northrook\Logger\Log;
use Northrook\Symfony\Core\Controller\Trait\ResponseMethods;
use Northrook\Symfony\Core\Response\ResponseHandler;
use Northrook\Symfony\Core\Service\CurrentRequest;
use Northrook\Symfony\Document;
use Support\Normalize;
use Symfony\Component\HttpFoundation\{HeaderBag, Response};
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\Profiler\Profiler;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Throwable;

/**
 * @internal
 * @property-read HeaderBag           $headerBag
 *
 * @author  Martin Nielsen <mn@northrook.com>
 *
 * @link    https://github.com/northrook Documentation
 *
 * @property-read HttpKernelInterface $httpKernel
 */
abstract class CoreController
{
    use ResponseMethods;

    protected readonly CurrentRequest $request;

    public function __get( string $property )
    {
        return match ( $property ) {
            'headerBag' => $this->request->headerBag(),
        };
    }

    /**
     * # `EntryPoint`.
     *
     * This is where all `domain.tld/admin/~` requests will be routed to.
     *
     * - `IF` the incoming request is tagged as a `HX` request, return only the content.
     * - `IF` the incoming request is generic, return full {@see DocumentResponse}.
     *
     * ---
     *
     * The `content` for each request will originate from a method either within this class,
     * or from any class within the {@see \Northrook\Symfony\Core\Controller\Admin} namespace.
     *
     * ---
     *
     * @param ?string         $route
     * @param Document        $document
     * @param ResponseHandler $response
     * @param Profiler        $profiler
     *
     * @return Response
     */
    final public function router(
        ?string         $route,
        Document        $document,
        ResponseHandler $response,
        Profiler        $profiler,
    ) : Response {
        dump( \get_defined_vars() );
        return $response( 'This is a routed response' );
    }

    /**
     * Parse an incoming route, converting it to a simple string for match comparison.
     *
     * @param null|string $route
     * @param ?string     $prefix
     *
     * @return string
     */
    final protected function routeHandler( ?string $route, ?string $prefix = null ) : string
    {
        $route = Normalize::key( [$prefix, $route] );

        return $route;
    }

    /**
     * Forwards the request to another controller.
     *
     * @param class-string|string                $controller The controller name (a string
     *                                                       like
     *                                                       "App\Controller\PostController::index"
     *                                                       or
     *                                                       "App\Controller\PostController"
     *                                                       if it is invokable)
     * @param array<string, class-string|string> $path
     * @param array<array-key, mixed>            $query
     *
     * @return Response
     */
    final protected function forward(
        string $controller,
        array  $path = [],
        array  $query = [],
    ) : Response {
        $request             = $this->request->current;
        $path['_controller'] = $controller;
        $subRequest          = $request->duplicate( $query, null, $path );

        try {
            return $this->request
                ->httpKernel()
                ->handle(
                    $subRequest,
                    HttpKernelInterface::SUB_REQUEST,
                );
        }
        catch ( Exception $e ) {
            Log::error( $e->getMessage() );
            return new Response(
                status : Response::HTTP_INTERNAL_SERVER_ERROR,
            );
        }
    }

    /**
     * Returns a NotFoundHttpException.
     *
     * This will result in a 404 response code.
     *
     * @param string                $message
     * @param ?Throwable            $previous
     * @param array<string, string> $headers
     *
     * @throws NotFoundHttpException
     */
    final protected function throwNotFoundException(
        string     $message = 'Not Found',
        ?Throwable $previous = null,
        array      $headers = [],
    ) : NotFoundHttpException {
        throw new NotFoundHttpException( $message, $previous, 404, $headers );
    }

    /**
     * Returns an AccessDeniedException.
     *
     * This will result in a 403 response code.
     *
     * @param string $message
     *
     * @param ?Throwable $previous
     * @param int        $code
     *
     * @throws AccessDeniedException
     */
    final protected function throwAccessDeniedException(
        string     $message = 'Access Denied',
        ?Throwable $previous = null,
        int        $code = Response::HTTP_FORBIDDEN,
    ) : AccessDeniedException {
        throw new AccessDeniedException( $message, $previous, $code );
    }
}
