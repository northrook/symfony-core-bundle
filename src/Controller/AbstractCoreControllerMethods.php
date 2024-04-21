<?php

namespace Northrook\Symfony\Core\Controller;

use Exception;
use Northrook\Symfony\Core\Services\CurrentRequestService;
use Northrook\Symfony\Core\Services\SecurityService;
use Psr\Log\LoggerInterface;
use SensitiveParameter;
use SplFileInfo;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Csrf\CsrfToken;

readonly abstract class AbstractCoreControllerMethods
{
    private readonly ?string $currentPath;
    private readonly ?string $currentRoute;

    protected RouterInterface       $router;
    protected SecurityService       $security;
    protected CurrentRequestService $request;
    protected ?LoggerInterface      $logger;


    /**
     * Forwards the request to another controller.
     *
     * @param string  $controller  The controller name (a string like "App\Controller\PostController::index" or "App\Controller\PostController" if it is invokable)
     */
    protected function forward( string $controller, array $path = [], array $query = [] ) : Response {
        $request               = $this->request->current;
        $path[ '_controller' ] = $controller;
        $subRequest            = $request->duplicate( $query, null, $path );

        try {
            return $this->httpKernel->handle( $subRequest, HttpKernelInterface::SUB_REQUEST );
        }
        catch ( Exception $e ) {
            $this->logger?->error( $e->getMessage() );
            return new Response(
                status : Response::HTTP_INTERNAL_SERVER_ERROR,
            );
        }
    }

    /**
     * Returns a JsonResponse that uses the serializer component if enabled, or json_encode.
     *
     * @param int  $status  The HTTP status code (200 "OK" by default)
     */
    protected function json( mixed $data, int $status = 200, array $headers = [], array $context = [] ) : JsonResponse {
        if ( $this->serializer ) {
            $context = array_merge( [ 'json_encode_options' => JsonResponse::DEFAULT_ENCODING_OPTIONS ], $context );
            $json    = $this->serializer->serialize(
                $data,
                'json',
                $context,
            );

            return new JsonResponse( $json, $status, $headers, true );
        }

        return new JsonResponse( $data, $status, $headers );
    }

    /**
     * Returns a BinaryFileResponse object with original or customized file name and disposition header.
     */
    protected function file(
        SplFileInfo | string $file,
        ?string              $fileName = null,
        string               $disposition = ResponseHeaderBag::DISPOSITION_ATTACHMENT,
    ) : BinaryFileResponse {
        $response = new BinaryFileResponse( $file );
        $response->setContentDisposition(
            $disposition,
            $fileName ?? $response->getFile()->getFilename(),
        );

        return $response;
    }

    /**
     * Checks if the attribute is granted against the current authentication token and optionally supplied subject.
     *
     */
    protected function isGranted( mixed $attribute, mixed $subject = null ) : bool {
        return $this->security->authorization->isGranted( $attribute, $subject );
    }

    /**
     * Checks the validity of a CSRF token.
     *
     * @param string       $id     The id used when generating the token
     * @param string|null  $token  The actual token sent with the request that should be validated
     */
    protected function isCsrfTokenValid(
        string $id, #[SensitiveParameter]
    ?string    $token,
    ) : bool {

        return $this->security->csrf->isTokenValid( new CsrfToken( $id, $token ) );
    }

}