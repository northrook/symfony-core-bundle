<?php

declare( strict_types = 1 );

namespace Northrook\Symfony\Core\DependencyInjection;

use Exception;
use Northrook\Logger\Log;
use Northrook\Symfony\Core\ErrorHandler\ErrorEventException;
use Northrook\Symfony\Core\Facade\Request;
use Northrook\Symfony\Core\Facade\URL;
use Northrook\Symfony\Core\Service\CurrentRequest;
use Stringable;
use Symfony\Component\Finder\SplFileInfo;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\HeaderBag;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Serializer\SerializerInterface;
use Throwable;


/**
 * @property-read HttpKernelInterface $httpKernel
 * @property-read HeaderBag           $headerBag
 *
 * @author  Martin Nielsen <mn@northrook.com>
 *
 * @link    https://github.com/northrook Documentation
 *
 * @internal
 */
abstract class CoreController
{
    protected readonly CurrentRequest $request;

    public function __get( string $property )
    {
        return match ( $property ) {
            'headerBag' => $this->request->headerBag(),
        };
    }

    /**
     * Return a {@see Response} with an arbitrary string.
     *
     * @param ?string  $content
     * @param int      $status
     * @param array    $headers
     *
     * @return Response
     */
    final protected function response( ?string $content = '', int $status = 200, array $headers = [] ) : Response
    {
        return new Response( $content, $status, $headers );
    }

    /**
     * Returns a JsonResponse that uses the serializer component if enabled, or
     * json_encode.
     *
     * - Will use any {@see SerializerInterface} assigned to
     * {@see $this->serializer}.
     * - Pass a {@see SerializerInterface} as the last argument to override the
     * default.
     * - If no serializer is available, json_encode will be used.
     *
     * @param mixed                     $data
     * @param int                       $status
     * @param array                     $headers
     * @param array                     $context
     * @param null|SerializerInterface  $serializer
     *
     * @return JsonResponse
     */
    final protected function jsonResponse(
            mixed                $data,
            int                  $status = Response::HTTP_OK,
            array                $headers = [],
            array                $context = [],
            ?SerializerInterface $serializer = null,
    ) : JsonResponse
    {
        if ( null === $serializer
             && \property_exists( $this, 'serializer' )
             && $this->serializer instanceof SerializerInterface
        ) {
            $serializer = $this->serializer;
        }

        if ( $serializer ) {
            $context = \array_merge( [ 'json_encode_options' => JsonResponse::DEFAULT_ENCODING_OPTIONS ], $context );
            $json    = $serializer->serialize( $data, 'json', $context );

            return new JsonResponse( $json, $status, $headers, true );
        }

        return new JsonResponse( $data, $status, $headers );
    }

    /**
     * Returns a {@see BinaryFileResponse} object with original or customized
     * file name and disposition header.
     */
    final protected function file(
            SplFileInfo | string $file,
            ?string              $fileName = null,
            string               $disposition = ResponseHeaderBag::DISPOSITION_ATTACHMENT,
    ) : BinaryFileResponse
    {
        $response = new BinaryFileResponse( $file );
        $fileName ??= $response->getFile()->getFilename();

        return $response->setContentDisposition( $disposition, $fileName );
    }

    /**
     * Forwards the request to another controller.
     *
     * @param string|class-string  $controller  The controller name (a string
     *                                          like
     *                                          "App\Controller\PostController::index"
     *                                          or
     *                                          "App\Controller\PostController"
     *                                          if it is invokable)
     */
    final protected function forward(
            string $controller,
            array  $path = [],
            array  $query = [],
    ) : Response
    {
        $request               = $this->request->current;
        $path[ '_controller' ] = $controller;
        $subRequest            = $request->duplicate( $query, null, $path );

        try {
            return $this->request
                    ->httpKernel()
                    ->handle(
                            $subRequest, HttpKernelInterface::SUB_REQUEST,
                    )
            ;
        }
        catch ( Exception $e ) {
            Log::error( $e->getMessage() );
            return new Response(
                    status : Response::HTTP_INTERNAL_SERVER_ERROR,
            );
        }
    }

    /**
     * Returns a RedirectResponse to the given URL.
     *
     * @param int  $status  The HTTP status code (302 "Found" by default)
     */
    final protected function redirect(
            string $url,
            int    $status = Response::HTTP_FOUND,
    ) : RedirectResponse
    {
        return new RedirectResponse( $url, $status );
    }

    /**
     * Returns a RedirectResponse to the given route with the given parameters.
     *
     * @param int  $status  The HTTP status code (302 "Found" by default)
     */
    final protected function redirectToRoute(
            string $route,
            array  $parameters = [],
            int    $status = Response::HTTP_FOUND,
    ) : RedirectResponse
    {
        try {
            $url = URL::get( $route, $parameters );
            Log::info( '{controller} is redirecting to {url}', [ 'controller' => $this::class, 'url' => $url ] );
            return $this->redirect( $url, $status );
        }
        catch ( Exception $exception ) {
            throw new ErrorEventException( previous : $exception );
        }
    }

    /**
     * Adds a simple flash message to the current session.
     *
     * @param string                   $type  = ['info', 'success', 'warning', 'error', 'notice'][$any]
     * @param string|Stringable|array  $message
     *
     * @return void
     */
    public function addFlash(
            string $type, string | Stringable | array $message,
    ) : void
    {
        Request::addFlash( $type, $message );
    }

    /**
     * Returns a NotFoundHttpException.
     *
     * This will result in a 404 response code.
     *
     * @param string      $message
     * @param ?Throwable  $previous
     * @param array       $headers
     *
     * @throws  NotFoundHttpException
     *
     */
    final protected function throwNotFoundException(
            string     $message = 'Not Found',
            ?Throwable $previous = null,
            array      $headers = [],
    ) : void
    {
        throw new NotFoundHttpException( $message, $previous, 404, $headers );
    }

    /**
     * Returns an AccessDeniedException.
     *
     * This will result in a 403 response code.
     *
     * @param ?Throwable  $previous
     *
     * @param string      $message
     *
     * @throws  AccessDeniedException
     *
     */
    final protected function throwAccessDeniedException(
            string      $message = 'Access Denied',
            ?\Throwable $previous = null,
            int         $code = Response::HTTP_FORBIDDEN,
    ) : void
    {
        throw new AccessDeniedException( $message, $previous, $code, );
    }

    // ::: Templating ::::

    /**
     * Parse an incoming route, converting it to a simple string for match comparison.
     *
     * @param null|string  $route
     *
     * @return string
     */
    final protected function routeHandler( ?string $route ) : string
    {
        dump( $route );

        return $route;
    }

}